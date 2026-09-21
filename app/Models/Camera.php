<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location', 'stream_url', 'source_type', 'type', 'status', 'active', 'last_heartbeat_at',
        'recording_enabled', 'recording_mode', 'retention_days', 'storage_quota_mb', 'resolution', 'fps',
        'schedule_start', 'schedule_end', 'flip_horizontal', 'flip_vertical', 'motion_detection', 'ptz_enabled',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
            'recording_enabled' => 'boolean',
            'retention_days' => 'integer',
            'storage_quota_mb' => 'integer',
            'fps' => 'integer',
            'schedule_start' => 'string',
            'schedule_end' => 'string',
            'flip_horizontal' => 'boolean',
            'flip_vertical' => 'boolean',
            'motion_detection' => 'boolean',
            'ptz_enabled' => 'boolean',
        ];
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class)->latest('started_at');
    }
}