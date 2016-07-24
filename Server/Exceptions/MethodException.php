<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Exception in method
 */
class MethodException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method exception', -32002, $data, $id);
    }
}