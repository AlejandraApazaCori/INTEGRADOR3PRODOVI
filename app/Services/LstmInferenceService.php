<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Process\Process;

class LstmInferenceService
{
    public function predict(array $payload): array
    {
        if (config('lstm.driver') === 'http') {
            return $this->predictRemotely($payload);
        }
        if (config('lstm.driver') !== 'local') {
            throw new RuntimeException('LSTM_DRIVER debe ser local o http.');
        }
        $process = new Process([
            config('lstm.python'), base_path('python/lstm_v4/runtime.py'), '--models', config('lstm.models'),
        ], base_path(), ['PYTHONIOENCODING' => 'utf-8'], json_encode($payload, JSON_THROW_ON_ERROR), config('lstm.timeout'));
        $process->run();
        if (! $process->isSuccessful()) {
            // No se envían credenciales al proceso y no se muestra stderr al navegador.
            throw new RuntimeException('No se pudo ejecutar el modelo LSTM. Verifica php artisan lstm:check.');
        }
        $result = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result['platforms'] ?? null)) {
            throw new RuntimeException('Respuesta LSTM inválida.');
        }

        return $result;
    }

    private function predictRemotely(array $payload): array
    {
        $url = rtrim((string) config('lstm.api_url'), '/');
        $parts = parse_url($url);
        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new RuntimeException('Configura LSTM_API_URL con la dirección HTTPS de la API LSTM.');
        }
        $token = (string) config('lstm.api_token');
        if (strlen($token) < 32) {
            throw new RuntimeException('Configura LSTM_API_TOKEN con la clave del servicio Python.');
        }
        try {
            $response = Http::acceptJson()->asJson()->withToken($token)
                ->connectTimeout(10)->timeout(config('lstm.timeout'))
                ->withOptions(['verify' => config('lstm.ca_bundle') ?: true])
                ->withoutRedirecting()->post($url.'/v1/predict', $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException('No se pudo contactar la API LSTM. Comprueba que el servicio esté encendido y que LSTM_API_URL sea la dirección vigente.');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if (($e->getHandlerContext()['errno'] ?? null) === 60) {
                throw new RuntimeException('PHP no puede verificar el certificado HTTPS de la API. Actualiza los certificados CA del servidor o configura LSTM_CA_BUNDLE.');
            }
            throw new RuntimeException('No se pudo completar la conexión HTTPS con la API LSTM.');
        }
        if (! $response->successful()) {
            $reason = match ($response->status()) {
                401, 403 => 'La clave de acceso LSTM no coincide.',
                413, 422 => 'La API LSTM rechazó el formato o tamaño del histórico.',
                429, 503 => 'La API LSTM está ocupada. Vuelve a intentarlo.',
                404, 502, 530 => 'La API LSTM no está disponible en esa dirección. Revisa LSTM_API_URL.',
                default => 'La API LSTM no pudo completar el cálculo.',
            };
            // Nunca incluir el cuerpo remoto, headers o credenciales en el log.
            throw new RuntimeException($reason.' HTTP '.$response->status().'.');
        }
        $result = $response->json();
        $networks = array_column($payload['accounts'] ?? [], 'platform');
        if (! is_array($result) || ! is_array($result['platforms'] ?? null)
            || count($result['platforms']) !== count($networks)
            || array_diff($networks, array_keys($result['platforms'])) !== []) {
            throw new RuntimeException('La API LSTM devolvió una respuesta incompatible.');
        }

        return $result;
    }
}
