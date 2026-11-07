<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'campaign_id', 'campaign_name', 'client_id', 'client_name',
        'user_id', 'user_name', 'action', 'task_id', 'task_title', 'changes',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    public function campaign()
    {
        return $this->belongsTo(Campania::class, 'campaign_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
