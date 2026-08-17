<?php

namespace App\Services\Libelula;

use App\Models\LibelulaTransaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LibelulaClient
{
    public function registerDebt(array $payload): array
    {
        return $this->post('/rest/deuda/registrar', $payload);
    }

    public function verifyPayment(LibelulaTransaction $transaction): array
    {
        return $this->post('/rest/deuda/consultar_deudas/por_identificador', [
            'identificador' => $transaction->identifier,
        ]);
    }

    private function post(string $path, array $payload): array
    {
        $appKey = (string) config('services.libelula.app_key');

        if ($appKey === '') {
            throw new RuntimeException('La pasarela de pagos no esta configurada.');
        }

        try {
            $response = $this->request()->post($path, [...$payload, 'appkey' => $appKey]);
        } catch (ConnectionException) {
            throw new RuntimeException('No fue posible comunicarse con Libelula.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Libelula respondio con un error HTTP.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Libelula devolvio una respuesta invalida.');
        }

        if (filter_var($data['error'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            throw new RuntimeException((string) ($data['mensaje'] ?? 'Libelula rechazo la solicitud.'));
        }

        return $data;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.libelula.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.libelula.timeout', 30));
    }
}
