<?php

namespace Rennokki\LaravelSnsEvents\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SnsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The payload to be delivered in the listeners.
     *
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param  array  $payload
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }
}
