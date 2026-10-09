<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Catat aktivitas ke tabel audit_logs
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        $user = Auth::user();
        $companyId = $user?->company_id ?? ($auditable?->company_id ?? 1);
        $userId = $user?->id ?? ($auditable instanceof User ? $auditable->id : 1);

        return AuditLog::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
