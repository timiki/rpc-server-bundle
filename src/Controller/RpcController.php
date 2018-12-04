<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry;

class RpcController extends AbstractController
{
    /**
     * @var \Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry
     */
    private $handlerRegistry;

    /**
     * RpcController constructor.
     *
     * @param \Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry $handlerRegistry
     */
    public function __construct(HttpHandlerRegistry $handlerRegistry)
    {
        $this->handlerRegistry = $handlerRegistry;
    }

    /**
     * @param Request $request
     * @param string  $version
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlerAction(Request $request, $version = 'default')
    {
        return $this
            ->handlerRegistry
            ->get($version)
            ->handleHttpRequest($request);
    }
}
