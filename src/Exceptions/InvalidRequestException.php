<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class InvalidRequestException extends ErrorException
{
    /**
     * InvalidRequestException constructor.
     *
     * @param mixed|null      $data
     * @param int|string|null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Invalid Request', -32600, $data, $id);
    }
}
