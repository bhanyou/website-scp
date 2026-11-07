<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $identity = trim($request->input('identity', $request->input('email', '')));
        $password = $request->input('password');

        if (empty($identity) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas (NIP/NISN/Username/Email) dan password wajib diisi.',
            ], 422);
        }

        // 1. Cari pengguna berdasarkan Email atau Username
        $user = User::where('email', $identity)
            ->orWhere('username', $identity)
            ->first();

        // 2. Jika belum ditemukan, periksa NIP Guru
        if (!$user) {
            $teacher = \App\Models\Teacher::where('nip', $identity)->first();
            if ($teacher && $teacher->user_id) {
                $user = User::find($teacher->user_id);
            }
        }

        // 3. Jika belum ditemukan, periksa NIS Siswa
        if (!$user) {
            $student = \App\Models\Student::where('nis', $identity)->first();
            if ($student && $student->user_id) {
                $user = User::find($student->user_id);
            }
        }

        // 4. Jika belum ditemukan dan diawali 'p' atau 'P' (Format Akun Orang Tua Siswa: contoh p12345 / psiswa1)
        if (!$user && (str_starts_with($identity, 'p') || str_starts_with($identity, 'P'))) {
            $candSub = substr($identity, 1);
            $candStudent = \App\Models\Student::where('nis', $candSub)
                ->orWhere('username', $candSub)
                ->first();
            if ($candStudent && $candStudent->parent && $candStudent->parent->user_id) {
                $user = User::find($candStudent->parent->user_id);
            }
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial login tidak cocok dengan data kami.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        $teacher = $user->teacher ?? Teacher::where('user_id', $user->id)->first();
        if (!$teacher && in_array($user->role, ['guru', 'bk'], true)) {
            $teacher = Teacher::where('name', $user->name)
                ->orWhere('username', $user->username)
                ->first();
            if ($teacher && empty($teacher->user_id)) {
                $teacher->update(['user_id' => $user->id]);
            }
        }

        $homeroomClasses = $teacher ? $teacher->homeroomClasses()->pluck('name')->all() : [];
        $isHomeroom = count($homeroomClasses) > 0;

        $activeStudent = $user->student ?? Student::where('user_id', $user->id)->first();
        if (!$activeStudent && $user->parent) {
            $activeStudent = $user->parent->students()->with('schoolClass')->first();
        }
        if (!$activeStudent && in_array($user->role, ['siswa', 'ortu'], true)) {
            $activeStudent = Student::where('username', $user->username)
                ->orWhere('name', $user->name)
                ->first();
            if ($activeStudent && empty($activeStudent->user_id)) {
                $activeStudent->update(['user_id' => $user->id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Login API berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'is_homeroom' => $isHomeroom,
                'homeroom_classes' => $homeroomClasses,
                'student' => $activeStudent ? [
                    'id' => $activeStudent->id,
                    'nis' => $activeStudent->nis,
                    'name' => $activeStudent->name,
                    'class_id' => $activeStudent->class_id,
                    'class_name' => $activeStudent->schoolClass?->name ?? '-',
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout API berhasil.',
        ]);
    }
}