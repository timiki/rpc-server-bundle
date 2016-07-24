<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Not granted exception.
 */
class MethodNotGrantedException extends ErrorException
{
    /**
     * @param null $data
     * @param null $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method not granted', -32001, $data, $id);
    }
}