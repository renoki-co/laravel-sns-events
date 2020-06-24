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
            $eventPayload = $this->getEventPayload($snsMessage, $request);

            if ($snsMessage['Type'] === 'SubscriptionConfirmation') {
                file_get_contents($snsMessage['SubscribeURL']);

                event(new SnsSubscriptionConfirmation($eventPayload));
            }

            if ($snsMessage['Type'] === 'Notification') {
                event(new SnsEvent($eventPayload));
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
            'headers' => $request->headers->all(),
        ];
    }
}
