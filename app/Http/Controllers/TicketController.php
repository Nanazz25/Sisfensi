<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketResponse;
use App\Models\SatisfactionRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Ticket::with(['reporter', 'category', 'operator']);

        if ($user->role === 'siswa') {
            $query->where('reporter_id', $user->id);
        } elseif ($user->role === 'helpdesk') {
            $query->orderByRaw("FIELD(priority, 'High', 'Mid', 'Low')");
        }

        // --- FILTERING ---
        if ($request->filled('status')) {
            if ($request->status === 'closed') {
                $query->whereIn('status', ['Resolved', 'Closed']);
            } elseif ($request->status === 'open') {
                $query->where('status', 'Open');
            }
        }

        // --- SORTING ---
        $sort = $request->get('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $tickets = $query->paginate(10)->withQueryString();

        // Stats for Siswa or Operator
        $stats = null;
        if ($user->role === 'siswa') {
            $stats = [
                'active' => Ticket::where('reporter_id', $user->id)->whereIn('status', ['Open', 'In-Progress'])->count(),
                'in_progress' => Ticket::where('reporter_id', $user->id)->where('status', 'In-Progress')->count(),
                'resolved' => Ticket::where('reporter_id', $user->id)->whereIn('status', ['Resolved', 'Closed'])->count(),
                'total' => Ticket::where('reporter_id', $user->id)->count(),
            ];
        } elseif ($user->role === 'helpdesk') {
            // Personal metrics for operator
            $avgResp = Ticket::where('operator_id', $user->id)
                ->whereNotNull('responded_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, responded_at)) as avg_min'))
                ->first()->avg_min ?? 0;
            
            $score = DB::table('satisfaction_ratings')
                ->join('tickets', 'satisfaction_ratings.ticket_id', '=', 'tickets.id')
                ->where('tickets.operator_id', $user->id)
                ->avg('rating') ?: 0;

            $stats = [
                'avg_response' => round($avgResp),
                'score_percent' => round(($score / 5) * 100),
                'active_incidents' => Ticket::whereIn('status', ['Open', 'In-Progress'])->count(),
            ];
        }

        return view('tickets.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        $categories = TicketCategory::all();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Mid,High',
        ]);

        Ticket::create([
            'reporter_id' => auth()->id(),
            'category_id' => $request->category_id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);

        return redirect()->route('tickets.index')->with('success', 'Tiket aduan berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        $ticket->load(['responses.responder', 'category', 'satisfactionRating', 'reporter']);
        
        $user = auth()->user();
        $suggestions = [];
        $suggestion = null;

        if (in_array($user->role, ['helpdesk', 'admin'])) {
            $suggestions = $this->generateSuggestions($ticket);
        }

        $view = in_array($user->role, ['helpdesk', 'admin']) ? 'tickets.operator_show' : 'tickets.show';
        
        return view($view, compact('ticket', 'suggestion', 'suggestions'));
    }

    public function searchSimilar(Request $request)
    {
        $subject = $request->get('subject', '');
        $description = $request->get('description', '');

        if (trim($subject) === '' && trim($description) === '') {
            return response()->json([]);
        }

        \Log::info("Searching similar tickets for: Subject: {$subject}, Desc: {$description}");

        $query = Ticket::query();
        
        // Gabungkan semua teks dan pecah jadi kata unik
        $allText = $subject . ' ' . $description;
        $searchTerms = array_unique(explode(' ', trim($allText)));
        $searchTerms = array_filter($searchTerms, function($term) {
            return strlen($term) >= 3; // Minimal 3 huruf agar pencarian berkualitas
        });

        if (empty($searchTerms)) {
            return response()->json([]);
        }

        // Cari yang mirip, tapi JANGAN tunjukkan tiket milik diri sendiri (opsional, tapi biasanya untuk menghindari duplikasi)
        // Namun untuk awal, kita tunjukkan semua yang mirip saja
        $query->where(function($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('subject', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            }
        });

        $similarTickets = $query->withoutGlobalScopes()
            ->latest()
            ->limit(5)
            ->get(['id', 'subject', 'status']);

        \Log::info("Found " . $similarTickets->count() . " similar tickets");

        return response()->json($similarTickets);
    }

    public function respond(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string']);

        $isFirstOperatorResponse = false;
        if (in_array(auth()->user()->role, ['helpdesk', 'admin']) && !$ticket->responded_at) {
            $isFirstOperatorResponse = true;
        }

        TicketResponse::create([
            'ticket_id' => $ticket->id,
            'responder_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $updateData = [];
        if ($isFirstOperatorResponse) {
            $updateData['responded_at'] = now();
            $updateData['operator_id'] = auth()->id();
            if ($ticket->status === 'Open') {
                $updateData['status'] = 'In-Progress';
            }
        }

        if (!empty($updateData)) {
            $ticket->update($updateData);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate(['status' => 'required|in:Open,In-Progress,Resolved,Closed']);
        
        $oldStatus = $ticket->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        // Catat operator jika status diubah dari Open ke In-Progress atau status akhir
        if ($newStatus !== 'Open' && !$ticket->operator_id) {
            $updateData['operator_id'] = auth()->id();
        }

        if (in_array($newStatus, ['Resolved', 'Closed']) && !in_array($oldStatus, ['Resolved', 'Closed'])) {
            $updateData['resolved_at'] = now();
            
            // Jika belum pernah dibalas, anggap respon pertama adalah saat penyelesaian ini
            if (!$ticket->responded_at) {
                $updateData['responded_at'] = now();
            }
        }

        $ticket->update($updateData);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function rate(Request $request, Ticket $ticket)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        SatisfactionRating::updateOrCreate(
            ['ticket_id' => $ticket->id],
            ['rating' => $request->rating, 'feedback' => $request->feedback]
        );

        return back()->with('success', 'Terima kasih atas penilaian Anda.');
    }

    public function adminDashboard()
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'Open')->count(),
            'in_progress' => Ticket::where('status', 'In-Progress')->count(),
            'closed' => Ticket::whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

        // Rata-rata response time per operator
        $operatorPerformance = DB::table('tickets')
            ->join('users', 'tickets.operator_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name as operator_name',
                DB::raw('COUNT(tickets.id) as tickets_handled'),
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.responded_at)) as avg_response_seconds'),
                DB::raw('AVG(TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.resolved_at)) as avg_resolution_seconds')
            )
            ->whereNotNull('tickets.operator_id')
            ->groupBy('users.id', 'users.name')
            ->get();

        // Rata-rata Rating Global
        $satisfactionAvg = SatisfactionRating::avg('rating') ?: 0;
        
        // Statistik Kepuasan per Operator
        $satisfactionStats = DB::table('satisfaction_ratings')
            ->join('tickets', 'satisfaction_ratings.ticket_id', '=', 'tickets.id')
            ->join('users', 'tickets.operator_id', '=', 'users.id')
            ->select(
                'users.name as operator_name',
                DB::raw('AVG(satisfaction_ratings.rating) as avg_rating'),
                DB::raw('COUNT(satisfaction_ratings.id) as ratings_count')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('avg_rating', 'desc')
            ->get();

        // Top 5 Operators (berdasarkan jumlah tiket ditangani)
        $topOperators = $operatorPerformance->sortByDesc('tickets_handled')->take(5);

        return view('tickets.admin_dashboard', compact('stats', 'operatorPerformance', 'satisfactionAvg', 'satisfactionStats', 'topOperators'));
    }

    private function authorizeView(Ticket $ticket)
    {
        $user = auth()->user();
        if ($user->role === 'siswa' && $ticket->reporter_id !== $user->id) {
            abort(403);
        }
    }

    public function fetchResponses(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        
        $responses = $ticket->responses()
            ->with('responder')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($resp) {
                return [
                    'id' => $resp->id,
                    'responder_id' => $resp->responder_id,
                    'responder_name' => $resp->responder->name,
                    'message' => nl2br(e($resp->message)),
                    'attachment' => $resp->attachment ? \Illuminate\Support\Facades\Storage::url($resp->attachment) : null,
                    'time' => $resp->created_at->format('H:i'),
                    'date' => $resp->created_at->format('d M'),
                    'is_me' => $resp->responder_id === auth()->id(),
                ];
            });

        return response()->json([
            'responses' => $responses,
            'suggestions' => $this->generateSuggestions($ticket)
        ]);
    }

    private function generateSuggestions(Ticket $ticket)
    {
        $lastResponse = $ticket->responses()->latest()->first();
        
        // Cari pesan terakhir dari PELAPOR (Siswa), bukan dari Operator
        $lastReporterMessage = $ticket->responses()->where('responder_id', $ticket->reporter_id)->latest()->first();
        $text = $lastReporterMessage ? strtolower($lastReporterMessage->message) : strtolower($ticket->description);
        
        $targetName = $ticket->reporter->name;

        // PRIORITAS 1: Deteksi Kata Kunci Khusus
        if (str_contains($text, 'lemot') || str_contains($text, 'lambat') || str_contains($text, 'loading')) {
            return ['Solusi Performa: "Halo ' . $targetName . ', mohon maaf atas ketidaknyamanannya. Coba hapus cache browser Anda atau gunakan koneksi internet lain, kami juga sedang mengecek kondisi server."'];
        }
        
        if (str_contains($text, 'absen') || str_contains($text, 'presensi') || str_contains($text, 'wajah')) {
            return ['Kendala Presensi: "Halo ' . $targetName . ', pastikan posisi wajah Anda pas di bingkai kamera dan ruangan cukup terang. Jika kamera tidak muncul, coba izinkan akses kamera di pengaturan browser."'];
        }

        if (str_contains($text, 'login') || str_contains($text, 'akun') || str_contains($text, 'password')) {
            return ['Masalah Akun: "Halo ' . $targetName . ', bisa sebutkan NIS atau Email Anda? Kami akan bantu cek status akun Anda dan melakukan reset jika diperlukan."'];
        }

        if (str_contains($text, 'gambar') || str_contains($text, 'foto') || str_contains($text, 'bukti')) {
            return ['Permintaan Bukti: "Baik ' . $targetName . ', mohon lampirkan tangkapan layar (screenshot) pesan error atau kendala tersebut agar kami bisa menganalisis lebih lanjut."'];
        }

        if (str_contains($text, 'makasih') || str_contains($text, 'terima kasih') || str_contains($text, 'siap')) {
            return ['Penyelesaian: "Sama-sama ' . $targetName . '. Senang bisa membantu. Apakah ada hal lain yang ingin ditanyakan sebelum tiket ini kami tutup?"'];
        }

        // PRIORITAS 2: Jika sudah ada respon operator, sarankan follow-up umum
        if ($lastResponse && $lastResponse->responder_id != $ticket->reporter_id) {
            return ['Tanya Kelanjutan: "Halo ' . $targetName . ', apakah kendala yang Anda alami sudah teratasi dengan langkah-langkah di atas?"'];
        }

        // PRIORITAS 3: Respon default jika baru mulai
        return ['Salam Pembuka: "Halo ' . $targetName . ', saya telah menerima aduan Anda mengenai ' . $ticket->subject . '. Mohon tunggu sebentar, saya sedang memeriksa detail masalahnya."'];
    }

    public function respondAsync(Request $request, Ticket $ticket)
    {
        $this->authorizeView($ticket);
        
        $request->validate([
            'message' => 'required_without:attachment|string|nullable',
            'attachment' => 'nullable|image|max:2048'
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tickets', 'public');
        }

        $response = $ticket->responses()->create([
            'responder_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        // Update status to In-Progress if it was Open
        if ($ticket->status === 'Open' && auth()->user()->role === 'helpdesk') {
            $ticket->update([
                'status' => 'In-Progress',
                'operator_id' => auth()->id(),
                'responded_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
