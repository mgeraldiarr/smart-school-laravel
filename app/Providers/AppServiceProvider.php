<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batas percobaan OTP dihitung per email (bukan per IP), karena lewat ngrok
        // semua request terlihat dari IP yang sama.
        RateLimiter::for('otp-send', fn (Request $request) => Limit::perMinute(3)
            ->by('otp-send|'.$this->otpKey($request))
            ->response($this->tooManyOtpAttempts(...)));

        RateLimiter::for('otp-verify', fn (Request $request) => Limit::perMinute(5)
            ->by('otp-verify|'.$this->otpKey($request))
            ->response($this->tooManyOtpAttempts(...)));
    }

    private function otpKey(Request $request): string
    {
        return strtolower(trim((string) $request->input('email'))) ?: $request->ip();
    }

    private function tooManyOtpAttempts(Request $request, array $headers)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam '.($headers['Retry-After'] ?? 60).' detik.',
        ], 429, $headers);
    }
}
