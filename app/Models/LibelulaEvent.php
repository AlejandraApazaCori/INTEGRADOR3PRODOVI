<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibelulaEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelula_transaction_record_id', 'libelula_transaction_id',
        'identifier', 'event_type', 'source', 'payload', 'processing_status',
        'error_message', 'received_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(LibelulaTransaction::class, 'libelula_transaction_record_id');
    }
}
