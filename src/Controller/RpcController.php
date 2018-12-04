<?php

namespace Timiki\Bundle\RpcServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RpcController extends Controller
{
    /**
     * @param Request $request
     * @param string  $version
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlerAction(Request $request, $version = '')
    {
        $handler = 'rpc.server.http_handler';

        if (null !== $version && '' !== $version) {
            $handler .= '.'.\mb_strtolower(\str_replace('.', '', $version));
        }

        if (true === $this->has($handler)) {
            return $this->get($handler)->handleHttpRequest($request);
        }

        throw new NotFoundHttpException(\sprintf('Rpc handler "%s" not found', $handler));
    }
}
