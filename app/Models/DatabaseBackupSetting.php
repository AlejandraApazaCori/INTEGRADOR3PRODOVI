<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DatabaseBackupSetting extends Model
{
    protected $fillable = [
        'frequency',
        'backup_time',
        'weekday',
        'month_day',
        'enabled',
        'next_run_at',
        'last_run_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'weekday' => 'integer',
            'month_day' => 'integer',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function calculateNextRun(?Carbon $from = null): ?Carbon
    {
        if (! $this->enabled) {
            return null;
        }

        $now = ($from ?: now())->copy();
        [$hour, $minute] = array_map('intval', explode(':', substr((string) $this->backup_time, 0, 5)));
        $candidate = $now->copy()->setTime($hour, $minute, 0);

        if ($this->frequency === 'weekly') {
            $targetDay = $this->weekday ?? 1;
            $daysToAdd = ($targetDay - $candidate->dayOfWeek + 7) % 7;
            $candidate->addDays($daysToAdd);
            if ($candidate->lessThanOrEqualTo($now)) {
                $candidate->addWeek();
            }

            return $candidate;
        }

        if ($this->frequency === 'monthly') {
            $day = min(28, max(1, $this->month_day ?? 1));
            $candidate->day($day);
            if ($candidate->lessThanOrEqualTo($now)) {
                $candidate->addMonthNoOverflow()->day($day);
            }

            return $candidate;
        }

        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate->addDay();
        }

        return $candidate;
    }
}
