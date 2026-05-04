<?php

namespace App\Listeners;

use Spatie\Activitylog\Events\LoggingActivity;

class SetActivityLogUser
{
    public function handle(LoggingActivity $event): void
    {
        // Set current user
        $event->causedBy(auth()->user());

        // Add IP address and User Agent to properties
        $event->withProperties([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
