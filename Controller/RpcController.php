<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RpcController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlerAction(Request $request)
    {
        return $this->get('rpc.server.http_handler')->handleHttpRequest($request);
    }
}