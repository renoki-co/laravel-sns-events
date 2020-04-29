<?php

namespace Rennokki\LaravelSnsEvents\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
     * The headers sent through the SNS request.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Create a new event instance.
     *
     * @param  array  $message
     * @param  array  $headers
     * @return void
     */
    public function __construct($message, $headers = [])
    {
        $this->message = $message;
        $this->headers = $headers;
    }
}
