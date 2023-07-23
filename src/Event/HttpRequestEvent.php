<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Contracts\EventDispatcher\Event;

class HttpRequestEvent extends Event
{
    public function __construct(private readonly HttpRequest $httpRequest)
    {
    }

    public function getHttpRequest(): HttpRequest
    {
        return $this->httpRequest;
    }
}
