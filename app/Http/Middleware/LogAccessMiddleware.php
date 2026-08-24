<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AccessLog;

class LogAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $durationMs = round(($endTime - $startTime) * 1000);

        // El sondeo automático del navbar no representa una navegación del usuario.
        if ($request->routeIs('administrador.notificaciones.conteo')) {
            return $response;
        }

        try {
            AccessLog::create([
                'ip_address' => $request->ip(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => $durationMs,
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $e) {
            // Silently fail if log creation fails so we don't block the request
        }

        return $response;
    }
}
