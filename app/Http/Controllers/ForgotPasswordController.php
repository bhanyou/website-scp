<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'identity' => ['nullable', 'string'],
            'email'    => ['nullable', 'string'],
        ]);

        $identity = trim($request->input('identity', $request->input('email', '')));

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

        if (!$user || empty($user->email)) {
            return back()->withErrors([
                'identity' => 'Identitas tidak ditemukan atau belum memiliki email terdaftar.',
            ])->withInput();
        }

        $key = 'forgot-password:' . $request->ip() . ':' . $user->email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors([
                'identity' => 'Terlalu banyak permintaan OTP. Coba lagi nanti.',
            ]);
        }

        RateLimiter::hit($key, 60);

        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => Carbon::now(),
            ]
        );

        try {
            Mail::send('emails.otp', ['user' => $user, 'otp' => $otp], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Kode Verifikasi OTP - School Class Progress');
            });
        } catch (\Throwable $e) {
            DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->delete();

            report($e);

            return back()->withErrors([
                'identity' => 'Email OTP gagal dikirim. Silakan periksa konfigurasi mail Anda.',
            ]);
        }

        $request->session()->put('otp_email', $user->email);

        // Buat masker email untuk privasi: contoh ah***@gmail.com
        $emailParts = explode('@', $user->email);
        $namePart = $emailParts[0];
        $domainPart = $emailParts[1] ?? 'gmail.com';
        $maskedName = substr($namePart, 0, 2) . str_repeat('*', max(3, strlen($namePart) - 2));
        $maskedEmail = $maskedName . '@' . $domainPart;

        return redirect()
            ->route('password.otp.form')
            ->with('status', "Kode OTP telah dikirim ke email terdaftar Anda ({$maskedEmail}).");
    }

    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('otp_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('otp_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (
            !$record ||
            !$record->created_at ||
            Carbon::parse($record->created_at)->addMinutes(5)->isPast() ||
            !Hash::check($request->otp, $record->token)
        ) {
            return back()->withErrors([
                'otp' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        $request->session()->put('otp_verified_email', $email);

        return redirect()->route('password.reset.form');
    }

    public function showResetForm(Request $request)
    {
        if (!$request->session()->has('otp_verified_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = $request->session()->get('otp_verified_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        $request->session()->forget([
            'otp_email',
            'otp_verified_email',
        ]);

        return redirect()->route('login')
            ->with('status', 'Password berhasil diubah. Silakan login kembali.');
    }
}

