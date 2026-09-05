<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class LstmInferenceService
{
    public function predict(array $payload): array
    {
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
}
