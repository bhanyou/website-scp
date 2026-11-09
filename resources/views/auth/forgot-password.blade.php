<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - School Class Progress</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="School Class Progress" style="width: 72px; height: 72px; object-fit: contain; margin: 0 auto 0.85rem auto; display: block;">
            <h1>Lupa Kata Sandi</h1>
            <p>Masukkan identitas akun untuk menerima kode verifikasi OTP</p>
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

        <form action="{{ route('password.otp.send') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="identity">NIP / NISN / Username / Email</label>
                <input
                    type="text"
                    id="identity"
                    name="identity"
                    value="{{ old('identity', old('email')) }}"
                    placeholder="Contoh: guru1, 12345678, atau email"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600; margin-top: 0.5rem;">
                Kirim Kode OTP
            </button>
            
            <div style="text-align: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('login') }}" style="color: #4f46e5; text-decoration: none; font-size: 0.875rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem;">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.775rem; color: var(--text-muted);">
            UKK Project • Sistem Informasi Akademik & Presensi
        </div>
    </div>

</body>
</html>
