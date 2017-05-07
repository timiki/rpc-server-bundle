<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

/**
 * Json parse exception.
 */
class ParseException extends ErrorException
{
    /**
     * ParseException constructor.
     */
    public function __construct()
    {
        parent::__construct('Parse error', -32700);
    }
}

