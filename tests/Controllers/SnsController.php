<?php

namespace Rennokki\LaravelSnsEvents\Tests\Controllers;

use Aws\Sns\MessageValidator;
use Illuminate\Http\Request;
use Rennokki\LaravelSnsEvents\Http\Controllers\SnsController as BaseSnsController;

class SnsController extends BaseSnsController
{
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
