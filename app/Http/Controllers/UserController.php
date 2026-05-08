<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private function getUsersByRole(Request $request, $role)
    {
        $query = User::where('role', $role);

        // 🔍 SEARCH
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        // ⬆⬇ SORT
        $sort = $request->get('sort', 'desc'); // default terbaru
        $query->orderBy('created_at', $sort);

        // 📄 PAGINATION
        return $query->paginate(10)->withQueryString();
    }

    public function admin(Request $request)
    {
        if (auth()->id() !== 1) {
            abort(403, 'Hanya Super Admin yang dapat mengakses halaman daftar admin.');
        }
        $users = $this->getUsersByRole($request, 'admin');
        return view('users.admin', compact('users'));
    }

    public function guru(Request $request)
    {
        $users = $this->getUsersByRole($request, 'guru');
        return view('users.guru', compact('users'));
    }

    public function helpdesk(Request $request)
    {
        $users = $this->getUsersByRole($request, 'helpdesk');
        return view('users.helpdesk', compact('users'));
    }

    public function siswa(Request $request)
    {
        $users = $this->getUsersByRole($request, 'siswa');
        return view('users.siswa', compact('users'));
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin' => redirect()->route('users.admin'),
            'guru' => redirect()->route('users.guru'),
            'siswa' => redirect()->route('users.siswa'),
            'helpdesk' => redirect()->route('users.helpdesk'),
            default => redirect()->route('users.index'),
        };
    }

    public function create()
    {
        return view('users.form', [
            'user' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,guru,siswa,helpdesk'
        ]);

        $plainPassword = $request->role === 'admin' ? 'password' : \Illuminate\Support\Str::random(8);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($plainPassword),
            'initial_password' => $plainPassword,
            'password_changed' => false,
        ]);

        return $this->redirectByRole($user->role)
            ->with('success', "User berhasil ditambahkan. Password: $plainPassword");
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,guru,siswa,helpdesk'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['password_changed'] = true;
        }

        $user->update($data);

        return $this->redirectByRole($user->role)
            ->with('success', 'User berhasil diupdate');
    }

    public function resetPassword(User $user)
    {
        $newPassword = \Illuminate\Support\Str::random(8);
        $user->update([
            'password' => Hash::make($newPassword),
            'initial_password' => $newPassword,
            'password_changed' => false
        ]);

        return back()->with('success', "Password {$user->name} berhasil direset menjadi: $newPassword");
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            if ($user->role === 'guru') {
                $user->teacher()?->delete();
            }

            if ($user->role === 'siswa') {
                $user->pesertaDidik()?->delete();
            }

            $user->delete();
        });

        return back()->with('success', 'User berhasil dihapus');
    }
}
