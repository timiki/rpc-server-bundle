<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RpcServerController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->get('rpc.server')->handleHttpRequest($request);
    }
}
