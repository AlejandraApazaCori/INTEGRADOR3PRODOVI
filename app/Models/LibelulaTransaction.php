<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibelulaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id', 'plan_id', 'identifier', 'libelula_transaction_id',
        'collection_code', 'payment_url', 'qr_url', 'customer_email',
        'customer_name', 'document_type_code', 'document_number',
        'document_complement', 'document_extension', 'business_name',
        'description', 'currency', 'expected_amount', 'status',
        'request_payload', 'response_payload', 'last_error', 'expires_at',
        'generated_at', 'confirmed_at', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'expires_at' => 'datetime',
            'generated_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function events()
    {
        return $this->hasMany(LibelulaEvent::class, 'libelula_transaction_record_id');
    }
}
