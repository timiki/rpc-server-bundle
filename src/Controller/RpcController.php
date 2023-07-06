<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistryInterface;

class RpcController
{
    public function __construct(private readonly HttpHandlerRegistryInterface $handlerRegistry)
    {
    }

    public function handlerAction(Request $request, string $version = 'default'): Response
    {
        return $this
            ->handlerRegistry
            ->get($version)
            ->handleHttpRequest($request);
    }
}
