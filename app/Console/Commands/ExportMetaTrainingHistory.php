<?php

namespace App\Console\Commands;

use App\Services\MetaPostHistoryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportMetaTrainingHistory extends Command
{
    protected $signature = 'meta:export-training-history {--account= : ID interno social_accounts opcional}';

    protected $description = 'Exporta un CSV real para Colab con la primera medición entre 48 y 54 horas de cada publicación';

    public function handle(MetaPostHistoryService $history): int
    {
        $seen = [];
        $rows = [];
        $query = DB::table('meta_post_snapshots')->orderBy('observed_at');
        if ($this->option('account')) {
            $query->where('social_account_id', $this->option('account'));
        }
        foreach ($query->cursor() as $row) {
            $age = Carbon::parse($row->published_at, 'UTC')->diffInHours(Carbon::parse($row->observed_at, 'UTC'), false);
            $key = $row->platform.'|'.$row->account_id.'|'.$row->post_id;
            if ($age < 48 || $age > 54 || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = $history->datasetRow($row, 'fixed_48h_tolerance_6h');
        }
        if ($rows === []) {
            $this->warn('Todavía no hay mediciones tomadas entre 48 y 54 horas. Mantén activo el scheduler.');

            return self::FAILURE;
        }
        $path = storage_path('app/private/dataset_meta_real_'.now()->format('Ymd_His').'.csv');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $stream = fopen($path, 'w');
        fputcsv($stream, array_keys($rows[0]), ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($stream, $row, ',', '"', '');
        }
        fclose($stream);
        $this->info(count($rows).' publicaciones exportadas: '.$path);

        return self::SUCCESS;
    }
}
