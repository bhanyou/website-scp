<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kata Sandi Baru - School Class Progress</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
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
            <h1>Buat Kata Sandi Baru</h1>
            <p>Masukkan kata sandi baru untuk akun SCP Anda</p>
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

        <form action="{{ route('password.reset') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="password">Kata Sandi Baru</label>
                <div style="position: relative; width: 100%;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        autofocus
                        style="width: 100%; padding-right: 2.75rem; box-sizing: border-box;"
                    >
                    <button
                        type="button"
                        id="togglePasswordBtn"
                        onclick="togglePasswordVisibility('password', 'eyeIcon1', 'eyeSlashIcon1')"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; display: none; align-items: center; justify-content: center; padding: 4px; z-index: 10;"
                        title="Lihat / Sembunyikan"
                    >
                        <svg id="eyeIcon1" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeSlashIcon1" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <div style="position: relative; width: 100%;">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Ulangi kata sandi baru"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        style="width: 100%; padding-right: 2.75rem; box-sizing: border-box;"
                    >
                    <button
                        type="button"
                        id="toggleConfirmBtn"
                        onclick="togglePasswordVisibility('password_confirmation', 'eyeIcon2', 'eyeSlashIcon2')"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #64748b; display: none; align-items: center; justify-content: center; padding: 4px; z-index: 10;"
                        title="Lihat / Sembunyikan"
                    >
                        <svg id="eyeIcon2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeSlashIcon2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600; margin-top: 0.5rem;">
                Simpan Kata Sandi Baru
            </button>
            
            <div style="text-align: center; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('login') }}" style="color: #4f46e5; text-decoration: none; font-size: 0.875rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem;">
                    &larr; Batalkan & Kembali ke Login
                </a>
            </div>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.775rem; color: var(--text-muted);">
            UKK Project • Sistem Informasi Akademik & Presensi
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pwd = document.getElementById('password');
            const pwdConf = document.getElementById('password_confirmation');
            const btn1 = document.getElementById('togglePasswordBtn');
            const btn2 = document.getElementById('toggleConfirmBtn');

            pwd.addEventListener('input', function () {
                btn1.style.display = this.value.length > 0 ? 'flex' : 'none';
            });

            pwdConf.addEventListener('input', function () {
                btn2.style.display = this.value.length > 0 ? 'flex' : 'none';
            });
        });

        function togglePasswordVisibility(inputId, eyeOpenId, eyeSlashId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeSlash = document.getElementById(eyeSlashId);

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeSlash.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeSlash.style.display = 'none';
            }
        }
    </script>
</body>
</html>
