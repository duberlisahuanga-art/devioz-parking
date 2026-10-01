<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
        'parking_id',
        'vehicle_id',

        'starts_on',
        'ends_on',

        'check_in_at',
        'check_out_at',
        'expected_duration_min',

        'status',

        'amount',
        'currency',
        'payment_status',

        'cancellation_status',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_reviewed_at',
        'cancellation_reviewed_by',
        'cancellation_review_note',
    ];

    protected $appends = [
        'amount_formatted',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',

            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',

            'expected_duration_min' => 'integer',

            'amount' => 'decimal:2',

            'cancellation_requested_at' => 'datetime',
            'cancellation_reviewed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Usuario
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Plaza
    |--------------------------------------------------------------------------
    */

    public function space(): BelongsTo
    {
        return $this->belongsTo(
            Space::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Estacionamiento
    |--------------------------------------------------------------------------
    */

    public function parking(): BelongsTo
    {
        return $this->belongsTo(
            Parking::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Vehículo
    |--------------------------------------------------------------------------
    */

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            Vehicle::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pagos
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Formato del monto
    |--------------------------------------------------------------------------
    */

    public function getAmountFormattedAttribute(): string
    {
        return 'S/ '
            . number_format(
                (float) $this->amount,
                2,
                '.',
                ','
            );
    }
}