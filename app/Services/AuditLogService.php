<?php

namespace App\Services;

use App\Models\AuditLogs;

class AuditLogService
{
    public function log($userId, $action, $modelType, $modelId, $changes = null)
    {
        AuditLogs::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'changes' => $changes,
        ]);
    }
}
