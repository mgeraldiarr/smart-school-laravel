<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Reset Kata Sandi</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <!-- Brand Header -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #0f172a; text-align: left;">
                            <span style="font-size: 16px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;">Smart School</span>
                            <span style="display: block; font-size: 11px; color: #94a3b8; margin-top: 2px;">Sistem Akademik & Presensi Presisi</span>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="font-size: 18px; font-weight: 600; color: #0f172a; margin: 0 0 12px 0;">Permintaan Pemulihan Kata Sandi</h1>
                            <p style="font-size: 13px; line-height: 20px; color: #475569; margin: 0 0 24px 0;">
                                Kami menerima permintaan untuk mereset kata sandi akun Smart School Anda. Masukkan kode verifikasi 6 digit berikut pada aplikasi untuk melanjutkan:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; text-align: center; margin: 0 0 24px 0;">
                                <span style="font-family: 'Courier New', Courier, monospace; font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #0f172a; display: block;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <p style="font-size: 12px; line-height: 18px; color: #64748b; margin: 0 0 16px 0;">
                                Kode ini hanya berlaku selama <strong>10 menit</strong>. Jika Anda tidak pernah merasa meminta pengaturan ulang kata sandi, abaikan surel ini demi keamanan akun Anda.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8; text-align: center;">
                            &copy; {{ date('Y') }} Smart School Administration. Hak cipta dilindungi.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
