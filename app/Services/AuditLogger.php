<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    public function write(string $action, ?string $subjectType, ?int $subjectId, string $summary, array $payload = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'summary' => $summary,
            'payload' => $payload,
            'ip' => request()->ip(),
        ]);
    }
}
