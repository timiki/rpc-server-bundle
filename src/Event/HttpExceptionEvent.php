<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

class HttpExceptionEvent extends HttpResponseEvent
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * HttpExceptionEvent constructor.
     */
    public function __construct(HttpResponse $httpResponse, \Exception $exception)
    {
        $this->exception = $exception;
        parent::__construct($httpResponse);
    }

    /**
     * Get exception.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
