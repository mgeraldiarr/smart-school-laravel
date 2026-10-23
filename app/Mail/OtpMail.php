<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email kode OTP reset kata sandi.
 * Dikirim lewat antrean agar request API tidak menunggu SMTP Gmail.
 */
class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Gmail kadang menolak sementara, coba ulang beberapa kali
    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public string $otp,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Reset Kata Sandi - Smart School',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
