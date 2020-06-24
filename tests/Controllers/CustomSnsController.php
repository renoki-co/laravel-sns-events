<?php

namespace Rennokki\LaravelSnsEvents\Tests\Controllers;

use Illuminate\Http\Request;
use Rennokki\LaravelSnsEvents\Http\Controllers\SnsController;

class CustomSnsController extends SnsController
{
    /**
     * Get the event payload to stream to the event.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getEventPayload(array $snsMessage, Request $request): array
    {
        return [
            'message' => $snsMessage,
            'test' => $request->query('test'),
        ];
    }
}
