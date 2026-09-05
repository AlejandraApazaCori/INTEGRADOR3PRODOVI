<?php

return [
    'python' => env('LSTM_PYTHON', base_path(PHP_OS_FAMILY === 'Windows' ? 'python/.venv-lstm/Scripts/python.exe' : 'python/.venv-lstm/bin/python')),
    'models' => env('LSTM_MODELS_PATH', base_path('python/modelos/meta_v4_20260905')),
    'timeout' => (int) env('LSTM_TIMEOUT', 120),
    'history_days' => 365,
];
