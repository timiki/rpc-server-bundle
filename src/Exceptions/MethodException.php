<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class MethodException extends ErrorException
{
    /**
     * MethodException constructor.
     *
     * @param mixed $data
     * @param mixed $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method exception', -32002, $data, $id);
    }
}
