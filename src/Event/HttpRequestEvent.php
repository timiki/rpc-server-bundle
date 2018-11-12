<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class HttpRequestEvent extends Event
{
    const EVENT = 'rpc.server.http.request';

    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * HttpRequestEvent constructor.
     *
     * @param HttpRequest $httpResponse
     */
    public function __construct(HttpRequest $httpResponse)
    {
        $this->httpRequest = $httpResponse;
    }

    /**
     * Get http request.
     *
     * @return HttpRequest
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }
}
