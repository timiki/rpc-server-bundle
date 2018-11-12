<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class InvalidParamsException extends ErrorException
{
    /**
     * InvalidParamsException constructor.
     *
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Invalid params', -32602, $data, $id);
    }
}
