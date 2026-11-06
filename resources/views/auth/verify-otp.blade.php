<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - School Class Progress</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <div class="brand-logo" style="margin: 0 auto; width: 48px; height: 48px; font-size: 1.4rem;">
                S
            </div>
            <h1>Verifikasi Kode OTP</h1>
            <p>Masukkan 6 digit kode yang dikirim ke email akun Anda</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" style="margin-bottom: 1.25rem;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
                <ul style="margin-left: 1rem; list-style: disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('password.otp.verify') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="otp" style="text-align: center; display: block;">Kode Verifikasi 6 Digit</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    required
                    autofocus
                    style="font-size: 1.6rem; letter-spacing: 0.5rem; text-align: center; font-weight: 700; padding: 0.75rem;"
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600; margin-top: 0.5rem;">
                Verifikasi OTP
            </button>
            
            <div style="text-align: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('password.request') }}" style="color: #4f46e5; text-decoration: none; font-size: 0.875rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem;">
                    &larr; Kirim Ulang / Ganti Akun
                </a>
            </div>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.775rem; color: var(--text-muted);">
            UKK Project • Sistem Informasi Akademik & Presensi
        </div>
    </div>

</body>
</html>
