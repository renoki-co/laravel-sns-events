<?php

namespace Rennokki\LaravelSnsEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rennokki\LaravelSnsEvents\Events\SnsEvent;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class SnsController extends Controller
{
    /**
     * Handle the incoming SNS event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle(Request $request)
    {
        $snsMessage = $this->getSnsMessage($request);

        if (isset($snsMessage['Type'])) {
            if ($snsMessage['Type'] === 'SubscriptionConfirmation') {
                file_get_contents($snsMessage['SubscribeURL']);

                $class = $this->getSubscriptionConfirmationEventClass();

                event(new $class(
                    $this->getSubscriptionConfirmationPayload($snsMessage, $request)
                ));
            }

            if ($snsMessage['Type'] === 'Notification') {
                $class = $this->getNotificationEventClass();

                event(new $class(
                    $this->getNotificationPayload($snsMessage, $request)
                ));
            }
        }

        return response('OK', 200);
    }

    /**
     * Get the payload content from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return null|string
     */
    protected function getRequestContent(Request $request)
    {
        return $request->getContent() ?: file_get_contents('php://input');
    }

    /**
     * Get the JSON-decoded content.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getSnsMessage(Request $request): array
    {
        return json_decode($this->getRequestContent($request), true);
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
        return SnsEvent::class;
    }
}
