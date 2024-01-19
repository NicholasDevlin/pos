<?php

namespace App\Events;

use App\Events\Interfaces\AuthenticationEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuccessfulRegister implements AuthenticationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $properties;

    /**
     * Create a new event instance.
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
