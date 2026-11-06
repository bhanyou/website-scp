<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP - SCP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155;">

    <!-- LATAR BELAKANG UTAMA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #0f172a; padding: 40px 15px;">
        <tr>
            <td align="center">
                
                <!-- CONTAINER KARTU UTAMA -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45); border: 1px solid rgba(255, 255, 255, 0.1);">
                    
                    <!-- HEADER ELEGAN -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); padding: 36px 30px 30px; color: #ffffff;">
                            
                            <!-- BADGE KEAMANAN -->
                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 9999px; padding: 5px 14px; margin-bottom: 14px;">
                                <span style="font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #c7d2fe;">
                                    Autentikasi Keamanan
                                </span>
                            </div>

                            <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 0.5px; color: #ffffff;">
                                School Class Progress
                            </h1>
                            <p style="margin: 6px 0 0; font-size: 13px; color: #a5b4fc; font-weight: 400;">
                                Sistem Informasi Presensi & Jurnal Kelas
                            </p>
                        </td>
                    </tr>

                    <!-- BADAN EMAIL -->
                    <tr>
                        <td style="padding: 36px 32px 30px;">
                            
                            <p style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #0f172a;">
                                Halo, {{ $user->name }}
                            </p>
                            <p style="margin: 0 0 28px; font-size: 14px; line-height: 1.6; color: #475569;">
                                Kami menerima permintaan pengaturan ulang kata sandi akun Anda. Masukkan kode OTP di bawah ini pada halaman verifikasi:
                            </p>

                            <!-- KOTAK KODE OTP HIGH-TECH -->
                            <div style="background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); border: 2px solid #c7d2fe; border-radius: 16px; padding: 24px 20px; text-align: center; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);">
                                <span style="display: block; font-size: 11px; font-weight: 700; color: #6366f1; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px;">
                                    KODE VERIFIKASI RESMI
                                </span>
                                
                                <span style="font-family: 'SF Mono', Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size: 38px; font-weight: 900; letter-spacing: 12px; color: #3730a3; display: inline-block; padding-left: 12px;">
                                    {{ $otp }}
                                </span>

                                <span style="display: block; font-size: 12px; color: #64748b; margin-top: 10px;">
                                    Berlaku selama <strong>5 menit</strong>
                                </span>
                            </div>

                            <!-- KARTU PERINGATAN KEAMANAN -->
                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
                                <p style="margin: 0; font-size: 12.5px; color: #991b1b; line-height: 1.5;">
                                    <strong>Peringatan Keamanan:</strong> Jangan pernah membagikan kode ini kepada siapa pun, termasuk pihak sekolah atau pengelola sistem.
                                </p>
                            </div>

                            <p style="margin: 0; font-size: 12.5px; color: #94a3b8; line-height: 1.5; text-align: center;">
                                Jika Anda tidak pernah meminta penggantian password, abaikan email ini. Akun Anda tetap aman.
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER MINIMALIS -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 22px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; font-weight: 600; color: #64748b;">
                                &copy; {{ date('Y') }} School Class Progress (SCP)
                            </p>
                            <p style="margin: 4px 0 0; font-size: 11px; color: #94a3b8;">
                                Pesan otomatis dibuat oleh server. Mohon untuk tidak membalas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- AKHIR KARTU UTAMA -->

            </td>
        </tr>
    </table>

</body>
</html>
