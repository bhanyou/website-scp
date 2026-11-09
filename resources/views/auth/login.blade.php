<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Class Progress</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Hilangkan ikon mata bawaan browser Edge / Windows */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="School Class Progress" style="width: 72px; height: 72px; object-fit: contain; margin: 0 auto 0.85rem auto; display: block;">
            <h1>School Class Progress</h1>
            <p>Silakan masuk dengan akun Anda</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
                <ul style="margin-left: 1rem; list-style: disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="identity">NIS / NIP / Username / Email</label>
                <input
                    type="text"
                    id="identity"
                    name="identity"
                    value="{{ old('identity', old('email')) }}"
                    placeholder="Masukkan NIS (Siswa), NIP (Guru), Username, atau Email"
                    required
                    autofocus
                >
            </div>

            <!-- Bagian Input Password dengan Fitur Mata -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label for="password" style="margin-bottom: 0;">Kata Sandi</label>
                </div>
                <div style="position: relative; width: 100%;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        style="width: 100%; padding-right: 2.75rem; box-sizing: border-box;"
                    >
                    <!-- Tombol Ikon Mata (Awalnya tersembunyi, muncul saat mulai mengetik) -->
                    <button
                        type="button"
                        id="togglePasswordBtn"
                        onclick="togglePasswordVisibility()"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; display: none; align-items: center; justify-content: center; padding: 4px; z-index: 10;"
                        title="Lihat / Sembunyikan Kata Sandi"
                        aria-label="Toggle password visibility"
                    >
                        <!-- Ikon Mata Terbuka (Default) -->
                        <svg id="eyeIcon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <!-- Ikon Mata Dicoret (Saat password terlihat) -->
                        <svg id="eyeSlashIcon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600; margin-top: 0.5rem;">
                Masuk ke Sistem
            </button>
            
            <div style="text-align: right; margin-top: 12px;">
                <a href="{{ route('password.request') }}"
                style="color: #4f46e5; text-decoration: none; font-size: 14px;">
                    Lupa Password?
                </a>
            </div>

        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.775rem; color: var(--text-muted);">
            UKK Project • Sistem Informasi Akademik & Presensi
        </div>
    </div>

    <!-- Script JavaScript untuk Tombol Mata -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePasswordBtn');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            // Tampilkan tombol mata HANYA ketika input password ada isinya (sedang diketik)
            passwordInput.addEventListener('input', function () {
                if (this.value.length > 0) {
                    toggleBtn.style.display = 'flex';
                } else {
                    toggleBtn.style.display = 'none';
                    // Reset ke mode password jika dihapus sampai kosong
                    this.type = 'password';
                    eyeIcon.style.display = 'block';
                    eyeSlashIcon.style.display = 'none';
                }
            });
        });

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeSlashIcon.style.display = 'none';
            }
        }
    </script>
</body>
</html>
