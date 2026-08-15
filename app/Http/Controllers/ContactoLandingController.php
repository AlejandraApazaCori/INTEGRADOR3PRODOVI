<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudContactoRequest;
use App\Mail\SolicitudContactoConfirmacion;
use App\Models\SolicitudContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactoLandingController extends Controller
{
    public function store(StoreSolicitudContactoRequest $request): JsonResponse|RedirectResponse
    {
        $solicitud = SolicitudContacto::create($request->validated());

        try {
            Mail::to($solicitud->correo)
                ->send(new SolicitudContactoConfirmacion($solicitud));

            $solicitud->forceFill([
                'correo_enviado_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar la solicitud de contacto por correo.', [
                'solicitud_contacto_id' => $solicitud->id,
                'message' => $exception->getMessage(),
            ]);

            $warning = 'Tu solicitud quedó registrada, pero no pudimos enviar la notificación por correo. Nos pondremos en contacto contigo con los datos guardados.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $warning,
                    'status' => 'warning',
                ], 202);
            }

            return redirect('/#contact')->with('contact_warning', $warning);
        }

        $success = '¡Gracias! Recibimos tu solicitud y nos pondremos en contacto contigo pronto.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $success,
                'status' => 'success',
            ], 201);
        }

        return redirect('/#contact')->with('contact_success', $success);
    }
}
