<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class ProxyException extends ErrorException
{
    /**
     * ProxyException constructor.
     *
     * @param mixed $data
     * @param mixed $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Proxy error', -32003, $data, $id);
    }
}
