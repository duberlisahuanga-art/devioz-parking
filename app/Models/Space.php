<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_id',
        'code',
        'floor',
        'type',
        'status',
        'current_reservation_id',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'current_reservation_id' => 'integer',
        ];
    }

    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function currentReservation(): BelongsTo
    {
        return $this->belongsTo(
            Reservation::class,
            'current_reservation_id'
        );
    }
}