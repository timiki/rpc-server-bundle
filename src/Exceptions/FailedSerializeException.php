<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class FailedSerializeException extends ErrorException
{
    /**
     * FailedSerializeException constructor.
     *
     * @param null|mixed      $data
     * @param null|int|string $id
     */
    public function __construct($data = null, $id = null)
    {
        parent::__construct('Method not granted', -32001, $data, $id);
    }
}
