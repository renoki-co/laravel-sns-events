<?php

namespace Rennokki\LaravelSnsEvents\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SnsSubscriptionConfirmation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The headers sent through the SNS request.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Create a new event instance.
     *
     * @param  array  $headers
     * @return void
     */
    public function __construct($headers = [])
    {
        $this->headers = $headers;
    }
}
