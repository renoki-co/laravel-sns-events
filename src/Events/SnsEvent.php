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
     * The headers sent through the SNS request.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Create a new event instance.
     *
     * @param  array  $payload
     * @param  array  $headers
     * @return void
     */
    public function __construct($payload, $headers = [])
    {
        $this->payload = $payload;
        $this->headers = $headers;
    }

    /**
     * Get the 'Message' from the notification payload.
     *
     * @return mixed
     */
    public function getMessage()
    {
        if (! isset($this->payload['Message'])) {
            return null;
        }

        $message = $this->payload['Message'];

        $decodedMessage = @json_decode($message, true);

        return $decodedMessage ?: $message;
    }
}
