<?php

namespace Rennokki\LaravelSnsEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rennokki\LaravelSnsEvents\Concerns\HandlesSns;
use Rennokki\LaravelSnsEvents\Events\SnsNotification;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class SnsController extends Controller
{
    use HandlesSns;

    /**
     * Handle the incoming SNS event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        if (! $this->snsMessageIsValid($request)) {
            return $this->okStatus();
        }

        $snsMessage = $this->getSnsMessage($request)->toArray();

        if (isset($snsMessage['Type'])) {
            if ($snsMessage['Type'] === 'SubscriptionConfirmation') {
                if (! $this->callSubscribeUrl($snsMessage['SubscribeURL'])) {
                    return $this->okStatus();
                }

                $class = $this->getSubscriptionConfirmationEventClass();

                event(new $class(
                    $this->getSubscriptionConfirmationPayload($snsMessage, $request)
                ));

                call_user_func([$this, 'onSubscriptionConfirmation'], $snsMessage, $request);
            }

            if ($snsMessage['Type'] === 'Notification') {
                $class = $this->getNotificationEventClass();

                event(new $class(
                    $this->getNotificationPayload($snsMessage, $request)
                ));

                call_user_func([$this, 'onNotification'], $snsMessage, $request);
            }
        }

        return $this->okStatus();
    }

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
            'headers' => $request->headers->all(),
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
        return $this->getNotificationPayload($snsMessage, $request);
    }

    /**
     * Get the event class to trigger during subscription confirmation.
     *
     * @return string
     */
    protected function getSubscriptionConfirmationEventClass(): string
    {
        return SnsSubscriptionConfirmation::class;
    }

    /**
     * Get the event class to trigger during SNS event.
     *
     * @return string
     */
    protected function getNotificationEventClass(): string
    {
        return SnsNotification::class;
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
        //
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
        //
    }

    /**
     * Get a 200 OK status.
     *
     * @return \Illuminate\Http\Response
     */
    protected function okStatus()
    {
        return response('OK', 200);
    }

    /**
     * Make a call to the subscribe URL to confirm the subscription.
     *
     * @param  string  $url
     * @return bool
     */
    protected function callSubscribeUrl(string $url): bool
    {
        return @file_get_contents($url) !== false;
    }
}
