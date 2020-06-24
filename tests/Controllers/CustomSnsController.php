<?php

namespace Rennokki\LaravelSnsEvents\Tests\Controllers;

use Illuminate\Http\Request;
use Rennokki\LaravelSnsEvents\Http\Controllers\SnsController;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSnsEvent;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSubscriptionConfirmation;

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

    /**
     * Get the event class to trigger during subscription confirmation.
     *
     * @return string
     */
    protected function getSubscriptionConfirmationEventClass(): string
    {
        return CustomSubscriptionConfirmation::class;
    }

    /**
     * Get the event class to trigger during SNS event.
     *
     * @return string
     */
    protected function getNotificationEventClass(): string
    {
        return CustomSnsEvent::class;
    }
}
