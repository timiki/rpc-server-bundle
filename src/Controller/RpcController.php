<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry;

class RpcController
{
    /**
     * @var HttpHandlerRegistry
     */
    private $handlerRegistry;

    public function __construct(HttpHandlerRegistry $handlerRegistry)
    {
        $this->handlerRegistry = $handlerRegistry;
    }

    /**
     * @param string $version
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function handlerAction(Request $request, $version = 'default')
    {
        return $this
            ->handlerRegistry
            ->get($version)
            ->handleHttpRequest($request);
    }
}
