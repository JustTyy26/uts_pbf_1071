<?php

namespace App\Http\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $jwt = $request->bearerToken();
        
        if (is_null($jwt)) {
            return response()->json(['Akses Ditolak'], 422);
        }

        try {
            $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['Token tidak valid'], 422);
        }
        
        $role = $decode->role ?? null;

        if (is_null($role)) {
            return response()->json(['Akses Ditolak'], 422);
        }

        $path = $request->path();

        if (str_starts_with($path, 'api/categories') && $role != 'admin') {
            return response()->json(['Anda tidak memiliki hak akses admin untuk kategori'], 422);
        }

        return $next($request);
    }
}
