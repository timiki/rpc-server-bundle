<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\EventDispatcher\Event;

class HttpResponseEvent extends Event
{
    /**
     * @var HttpResponse
     */
    private $httpResponse;

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
