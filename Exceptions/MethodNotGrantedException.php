<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

/**
 * Not granted exception.
 */
class MethodNotGrantedException extends ErrorException
{
    /**
     * MethodNotGrantedException constructor.
     *
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method not granted', -32001, $data, $id);
    }
}