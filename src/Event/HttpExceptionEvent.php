<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

class HttpExceptionEvent extends HttpResponseEvent
{
    private \Exception $exception;

    public function __construct(HttpResponse $httpResponse, \Exception $exception)
    {
        $this->exception = $exception;
        parent::__construct($httpResponse);
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }
}
