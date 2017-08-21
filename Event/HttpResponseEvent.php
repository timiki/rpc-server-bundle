<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class HttpResponseEvent extends Event
{
    const EVENT = 'rpc.server.http.response';

    /**
     * @var HttpResponse
     */
    private $httpResponse;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * HttpResponseEvent constructor.
     *
     * @param HttpResponse $httpResponse
     */
    public function __construct(HttpResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * Get http response.
     *
     * @return HttpResponse
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }
}