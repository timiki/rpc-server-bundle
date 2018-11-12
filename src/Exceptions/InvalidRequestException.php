<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

/**
 * Invalid request exception.
 */
class InvalidRequestException extends ErrorException
{
    /**
     * InvalidRequestException constructor.
     *
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Invalid Request', -32600, $data, $id);
    }
}
