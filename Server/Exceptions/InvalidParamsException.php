<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Invalid params exception.
 */
class InvalidParamsException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Invalid params', -32602, $data, $id);
    }
}

