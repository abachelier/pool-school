<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastConnectedAt
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $event->user->update([
            'last_connected_at' => now(),
        ]);
    }
}
