<?php

namespace Rennokki\LaravelSnsEvents\Http\Controllers;

use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Routing\Controller;
use Rennokki\LaravelSnsEvents\Events\SnsEvent;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class SnsController extends Controller
{
    public function handle()
    {
        // Instantiate the Message and Validator
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        // Validate the message.
        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            // Return 404 to pretend we are not here for SNS if invalid request
            return response('SNS Message Validation Error: ' . $e->getMessage(), 404);
        }

        // Check the type of the message and handle the subscription.
        if ($message['Type'] === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            file_get_contents($message['SubscribeURL']);
            event(new SnsSubscriptionConfirmation);
            return response('OK', 200);
        }

        if ($message['Type'] === 'Notification') {
            event(new SnsEvent($message));
        }

        return response('OK', 200);
    }
}
