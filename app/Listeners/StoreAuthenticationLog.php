<?php

namespace App\Listeners;

use App\Events\Interfaces\AuthenticationEvent;
use App\Events\SuccessfulLogin;
use App\Events\SuccessfulRegister;
use App\Events\UnsuccessfulLogin;
use App\Models\UserManagement\AuthenticationLog;

class StoreAuthenticationLog
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuthenticationEvent $event): void
    {
        $log_name = match (get_class($event)) {
            SuccessfulLogin::class => 'login',
            UnsuccessfulLogin::class => 'failed',
            SuccessfulRegister::class => 'registered',
            default => '',
        };

        $properties = $event->properties ?? null;

        AuthenticationLog::create(compact('log_name', 'properties'));
    }
}
