<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RpcController extends Controller
{
	/**
	 * @Route("/rpc")
	 */
    public function indexAction(Request $request)
    {
        return $this->get('rpc.server')->handleHttpRequest($request);
    }
}
