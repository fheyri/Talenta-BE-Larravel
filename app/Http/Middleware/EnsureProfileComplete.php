<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_profile_complete) {
            return response()->json([
                'message' => 'Lengkapi data diri (nama, sekolah, jurusan) terlebih dahulu.',
                'code' => 'profile_incomplete',
            ], 403);
        }

        return $next($request);
    }
}
