<?php

namespace Rennokki\LaravelSnsEvents\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SnsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message to be delivered in the listeners.
     *
     * @var array
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  array  $message
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}
