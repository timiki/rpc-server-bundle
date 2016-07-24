<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RpcController extends Controller
{
    /**
     * @Route("/rpc")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rpcAction(Request $request)
    {
        return $this->get('rpc.server.handler')->handleHttpRequest($request);
    }
}