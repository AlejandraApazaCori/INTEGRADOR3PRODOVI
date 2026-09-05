<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\MetaCampaignAnalyticsService;
use Illuminate\Console\Command;

class SyncMetaTrainingHistory extends Command
{
    protected $signature = 'meta:sync-training-history';

    protected $description = 'Recoge métricas reales de publicaciones recientes para el histórico LSTM';

    public function handle(MetaCampaignAnalyticsService $analytics): int
    {
        $failed = false;
        foreach (SocialAccount::whereIn('provider', ['facebook_page', 'instagram'])->cursor() as $account) {
            try {
                $result = $analytics->forPublishingAccounts([$account], 7);
                if ($result['errors'] !== []) {
                    $failed = true;
                    $this->warn("Cuenta {$account->id}: Meta no devolvió todas las métricas.");
                }
            } catch (\Throwable $e) {
                $failed = true;
                $this->warn("Cuenta {$account->id}: no se pudo sincronizar.");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
