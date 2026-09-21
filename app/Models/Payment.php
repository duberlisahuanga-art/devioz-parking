<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const METHODS = [
        'yape',
        'plin',
        'card_bcp',
        'card_bbva',
        'card_interbank',
        'card_other',
    ];

    public const PROVIDERS = [
        'culqi',
        'niubiz',
        'izipay',
        'manual',
    ];

    protected $fillable = [
        'reservation_id',
        'amount',
        'method',
        'provider',
        'provider_tx_id',
        'voucher_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
