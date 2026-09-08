<?php

$apiUrl = trim((string) env('LSTM_API_URL', ''));
$driver = trim((string) env('LSTM_DRIVER', ''));

return [
    // Una URL configurada implica HTTP aunque el proveedor conserve una variable vacía.
    'driver' => $driver !== '' ? $driver : ($apiUrl !== '' ? 'http' : 'local'),
    'api_url' => $apiUrl,
    'api_token' => env('LSTM_API_TOKEN'),
    'ca_bundle' => env('LSTM_CA_BUNDLE'),
    'model_revision' => env('LSTM_MODEL_REVISION', 'meta_v4_20260905'),
    'python' => env('LSTM_PYTHON', base_path(PHP_OS_FAMILY === 'Windows' ? 'python/.venv-lstm/Scripts/python.exe' : 'python/.venv-lstm/bin/python')),
    'models' => env('LSTM_MODELS_PATH', base_path('python/modelos/meta_v4_20260905')),
    'timeout' => (int) env('LSTM_TIMEOUT', 120),
    'history_days' => 365,
];
