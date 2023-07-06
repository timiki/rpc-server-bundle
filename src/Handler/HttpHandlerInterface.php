<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

interface HttpHandlerInterface
{
    public function handleHttpRequest(HttpRequest $httpRequest): HttpResponse;
}
