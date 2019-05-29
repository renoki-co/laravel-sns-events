<?php

namespace Rennokki\LaravelSnsEvents\Http\Controllers;

use Illuminate\Routing\Controller;
use Rennokki\LaravelSnsEvents\Events\SnsEvent;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class SnsController extends Controller
{
    public function handle()
    {
        $message = json_decode(file_get_contents('php://input'), true);

        if (isset($message['Type']) && $message['Type'] === 'SubscriptionConfirmation') {
            file_get_contents($message['SubscribeURL']);

            event(new SnsSubscriptionConfirmation);

            return response('OK', 200);
        }

        event(new SnsEvent($message));

        return response('OK', 200);
    }
}
