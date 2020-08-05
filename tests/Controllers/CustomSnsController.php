<?php

namespace Rennokki\LaravelSnsEvents\Tests\Controllers;

use Aws\Sns\MessageValidator;
use Illuminate\Http\Request;
use Rennokki\LaravelSnsEvents\Http\Controllers\SnsController;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSnsEvent;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSubscriptionConfirmation;

class CustomSnsController extends SnsController
{
    /**
     * Get the event payload to stream to the event in case
     * AWS sent a notification payload.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getNotificationPayload(array $snsMessage, Request $request): array
    {
        return [
            'message' => $snsMessage,
            'test' => $request->query('test'),
        ];
    }

    /**
     * Get the event payload to stream to the event in case
     * AWS sent a subscription confirmation payload.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getSubscriptionConfirmationPayload(array $snsMessage, Request $request): array
    {
        return [
            'message' => $snsMessage,
            'confirmation_test' => $request->query('test'),
            'on_subscription_confirmation' => $request->onSubscriptionConfirmation,
            'on_notification' => $request->onNotification,
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

    /**
     * Handle logic at the controller level on notification.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function onNotification(array $snsMessage, Request $request): void
    {
        mt_rand(0, 10000);
    }

    /**
     * Handle logic at the controller level on subscription.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function onSubscriptionConfirmation(array $snsMessage, Request $request): void
    {
        mt_rand(0, 10000);
    }

    /**
     * Get the message validator instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Aws\Sns\MessageValidator
     */
    protected function getMessageValidator(Request $request)
    {
        return new MessageValidator(function ($url) use ($request) {
            return $request->certificate ?: $url;
        });
    }
}
