<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\AuditLog;

class AuditObserver
{
    public function created($model): void
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated($model): void
    {
        $changes = $model->getChanges();
        $sensitive = ['password', 'remember_token'];

        if (empty($changes)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($changes as $key => $newValue) {
            if ($key === 'updated_at' || in_array($key, $sensitive)) {
                continue;
            }
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        if (empty($oldValues) && empty($newValues)) {
            return;
        }

        $this->log('updated', $model, $oldValues, $newValues);
    }

    public function deleted($model): void
    {
        $this->log('deleted', $model, $model->getAttributes(), null);
    }

    private function log(string $action, $model, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
