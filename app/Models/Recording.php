<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recording extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_id', 'user_id', 'path', 'triggered_by', 'started_at', 'ended_at',
        'duration_sec', 'size_mb', 'status',
    ];

    protected $appends = ['duration_formatted'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_sec' => 'integer',
            'size_mb' => 'decimal:2',
        ];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        $seconds = (int) ($this->duration_sec ?? 0);

        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}