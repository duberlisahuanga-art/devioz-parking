<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'duration_min', 'active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'duration_min' => 'integer', 'active' => 'boolean'];
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_services')->withPivot('price')->withTimestamps();
    }
}