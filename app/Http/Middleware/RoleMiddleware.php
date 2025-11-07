<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user || !in_array(strtolower($user->role), $roles)) {
            // === CEK APAKAH INI AJAX / API ===
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Anda tidak memiliki akses ke sumber daya ini.',
                    'code'  => 403
                ], 403);
            }

            // === JIKA BUKAN AJAX → HALAMAN 403 BIASA ===
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}