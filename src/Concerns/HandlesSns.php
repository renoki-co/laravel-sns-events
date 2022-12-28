<?php

namespace Rennokki\LaravelSnsEvents\Concerns;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait HandlesSns
{
    /**
     * Get the SNS message as array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Aws\Sns\Message
     */
    public function getSnsMessage(Request $request)
    {
        try {
            return Message::fromJsonString(
                $request->getContent() ?: file_get_contents('php://input')
            );
        } catch (Exception $e) {
            return new Message([]);
        }
    }

    /**
     * Check if the SNS message is valid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function snsMessageIsValid(Request $request): bool
    {
        try {
            return $this->getMessageValidator($request)->isValid(
                $this->getSnsMessage($request)
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the message validator instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Aws\Sns\MessageValidator
     */
    protected function getMessageValidator(Request $request)
    {
        if (App::environment(['testing', 'local'])) {
            return new MessageValidator(function ($url) use ($request) {
                if ($certificate = $request->sns_certificate) {
                    return $certificate;
                }

                if ($certificate = $request->header('X-Sns-Testing-Certificate')) {
                    return $certificate;
                }

                return @file_get_contents($url);
            });
        }

        return new MessageValidator;
    }
}
