<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Proxy exception.
 */
class ProxyException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Proxy error', -32003, $data, $id);
    }
}

