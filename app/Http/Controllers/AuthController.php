<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identity' => ['nullable', 'string'],
            'email'    => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($request->input('identity', $request->input('email', '')));
        $password = $request->input('password');

        if (empty($identity)) {
            return back()->withErrors([
                'identity' => 'Silakan masukkan NIP, NISN, Username, atau Email.',
            ])->withInput();
        }

        // 1. Cari pengguna berdasarkan Email atau Username
        $user = User::where('email', $identity)
            ->orWhere('username', $identity)
            ->first();

        // 2. Jika belum ditemukan, periksa NIP Guru
        if (!$user) {
            $teacher = Teacher::where('nip', $identity)->first();
            if ($teacher && $teacher->user_id) {
                $user = User::find($teacher->user_id);
            }
        }

        // 3. Jika belum ditemukan, periksa NIS Siswa
        if (!$user) {
            $student = Student::where('nis', $identity)->first();
            if ($student && $student->user_id) {
                $user = User::find($student->user_id);
            }
        }

        // 4. Jika belum ditemukan dan diawali 'p' atau 'P' (Format Akun Orang Tua Siswa: contoh p12345 / psiswa1)
        if (!$user && (str_starts_with($identity, 'p') || str_starts_with($identity, 'P'))) {
            $candSub = substr($identity, 1);
            $candStudent = Student::where('nis', $candSub)
                ->orWhere('username', $candSub)
                ->first();
            if ($candStudent && $candStudent->parent && $candStudent->parent->user_id) {
                $user = User::find($candStudent->parent->user_id);
            }
        }

        // 5. Verifikasi kecocokan kata sandi
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            return match ($user->role) {
                'waka'          => redirect()->route('dashboard.waka'),
                'guru', 'bk'    => redirect()->route('dashboard.guru'),
                'siswa', 'ortu' => redirect()->route('dashboard.siswa'),
                default         => redirect()->route('home'),
            };
        }

        return back()->withErrors([
            'identity' => 'Kredensial login tidak cocok dengan data kami.',
        ])->withInput($request->only('identity', 'email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}