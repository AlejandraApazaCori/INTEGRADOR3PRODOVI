<?php

namespace App\Console\Commands;

use App\Services\LstmInferenceService;
use Illuminate\Console\Command;

class LstmCheck extends Command
{
    protected $signature = 'lstm:check';

    protected $description = 'Verifica carga y salidas de los modelos LSTM sin consultar ni publicar en Meta';

    public function handle(LstmInferenceService $service): int
    {
        try {
            $result = $service->predict(['health' => true, 'accounts' => [['platform' => 'facebook'], ['platform' => 'instagram']]]);
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
