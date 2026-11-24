<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi rute API untuk peran tertentu, misal `role:teacher` atau
 * `role:teacher,admin`. Tanpa ini, token wali murid bisa memanggil
 * endpoint guru (mengubah presensi atau nilai).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Akun yang dinonaktifkan admin tidak boleh memakai token lama.
        if (! $user || ! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun ini tidak aktif. Hubungi admin sekolah.',
            ], 403);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        return $next($request);
    }
}
