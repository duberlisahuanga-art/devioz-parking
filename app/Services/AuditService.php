<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(
        Request $request,
        string $module,
        string $action,
        string $description,
        ?Model $entity = null,
        ?string $reference = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $request->user()?->id,

            'module' => $module,

            'action' => $action,

            'entity_type' => $entity
                ? $entity::class
                : null,

            'entity_id' => $entity?->getKey(),

            'reference' => $reference,

            'description' => $description,

            'metadata' => $metadata,

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),
        ]);
    }
}