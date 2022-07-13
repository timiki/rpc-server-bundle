<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class MethodException extends ErrorException
{
    /**
     * MethodException constructor.
     *
     * @param mixed|null      $data
     * @param int|string|null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method exception', -32002, $data, $id);
    }
}
