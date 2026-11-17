<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LoginAudit;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a general audit event.
     */
    public static function log(
        string $event,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a login attempt.
     */
    public static function logLogin(
        string $usernameEntered,
        string $status,
        ?int $userId = null,
        ?string $reason = null
    ): LoginAudit {
        return LoginAudit::create([
            'user_id' => $userId,
            'username_entered' => $usernameEntered,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'status' => $status,
            'reason' => $reason,
            'logged_at' => now(),
        ]);
    }
}
