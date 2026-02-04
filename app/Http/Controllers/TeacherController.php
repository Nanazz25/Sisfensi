<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private function generateNip()
    {
        $lastNip = Teacher::whereNotNull('nip')
            ->orderBy('nip', 'desc')
            ->value('nip');

        return $lastNip
            ? (string) ((int) $lastNip + 1)
            : '19800101001';
    }

    public function index(Request $request)
    {
        $q = $request->q;
        $sort = $request->sort === 'asc' ? 'asc' : 'desc';

        $teachers = Teacher::with('user')
            ->when($q, function ($query) use ($q) {
                $query->where('nip', 'like', "%{$q}%")
                    ->orWhere('nama_lengkap', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($sub) use ($q) {
                        $sub->where('name', 'like', "%{$q}%");
                    });
            })
            ->orderBy('created_at', $sort)
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        $users = User::where('role', 'guru')
            ->whereDoesntHave('teacher')
            ->get();

        return view('teachers.form', [
            'teacher' => null,
            'users' => $users,
            'generatedNip' => $this->generateNip(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nama_lengkap' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        Teacher::create([
            'user_id' => $request->user_id,
            'nama_lengkap' => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nip' => $this->generateNip(),
            'nuptk' => $request->nuptk,
            'nik' => $request->nik,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Guru berhasil ditambahkan');
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.form', [
            'teacher' => $teacher,
            'users' => collect(),
            'generatedNip' => $teacher->nip,
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'nip' => 'required|unique:teachers,nip,' . $teacher->id,
            'nama_lengkap' => 'required',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $teacher->update($request->only([
            'nama_lengkap',
            'nip',
            'nuptk',
            'nik',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
        ]));

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil diperbarui');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return back()->with('success', 'Guru berhasil dihapus');
    }
}
