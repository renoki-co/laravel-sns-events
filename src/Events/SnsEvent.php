<?php

namespace Rennokki\LaravelSnsEvents\Events;

class SnsEvent
{
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
