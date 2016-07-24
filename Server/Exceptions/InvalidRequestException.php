<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Invalid request exception.
 */
class InvalidRequestException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Invalid Request', -32600, $data, $id);
    }
}

