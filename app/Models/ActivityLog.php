<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    protected $table = 'activity_log';

    public function getModelName()
    {
        if ($this->subject_type) {
            $parts = explode('\\', $this->subject_type);
            return end($parts);
        }
        return 'System';
    }

    public function getChangesAttribute()
    {
        $changes = [];
        
        if ($this->properties && isset($this->properties['attributes'])) {
            $attributes = $this->properties['attributes'];
            $old = $this->properties['old'] ?? [];

            foreach ($attributes as $key => $newValue) {
                $oldValue = $old[$key] ?? null;
                
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }
}
