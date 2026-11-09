<?php

namespace App\Traits;

use App\Models\Microfinance\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity($event)
    {
        $oldValues = $event === 'updated' ? array_intersect_key($this->getOriginal(), $this->getDirty()) : null;
        $newValues = $event === 'updated' ? $this->getDirty() : ($event === 'created' ? $this->getAttributes() : null);

        // Filter out sensitive or unnecessary fields
        $ignoredFields = ['updated_at', 'created_at', 'deleted_at', 'password', 'remember_token'];
        if ($oldValues) {
            foreach ($ignoredFields as $field) unset($oldValues[$field]);
        }
        if ($newValues) {
            foreach ($ignoredFields as $field) unset($newValues[$field]);
        }

        // Only log if there are changes (for updates)
        if ($event === 'updated' && empty($newValues)) {
            return;
        }

        ActivityLog::create([
            'sister_concern_id' => Auth::check() ? Auth::user()->activeConcernId() : ($this->sister_concern_id ?? null),
            'user_id' => Auth::id(),
            'event' => $event,
            'loggable_id' => $this->id,
            'loggable_type' => get_class($this),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
