<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Method not found exception.
 */
class MethodNotFoundException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method not found', -32601, $data, $id);
    }
}

