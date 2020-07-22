<?php

namespace Rennokki\LaravelSnsEvents\Concerns;

use Illuminate\Http\Request;

trait HandlesSns
{
    /**
     * Get the payload content from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return null|string
     */
    public function getRequestContent(Request $request)
    {
        return $request->getContent() ?: file_get_contents('php://input');
    }

    /**
     * Get the JSON-decoded content.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getSnsMessage(Request $request): array
    {
        return json_decode($this->getRequestContent($request), true);
    }
}
