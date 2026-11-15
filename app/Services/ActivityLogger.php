<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function record(?User $actor, string $action, ?Model $subject = null, array $properties = []): void
    {
        $request = app()->bound('request') ? app(Request::class) : null;

        ActivityLog::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'properties' => $properties,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
            ...($subject ? ['subject_type' => $subject->getMorphClass(), 'subject_id' => $subject->getKey()] : []),
        ]);
    }
}
