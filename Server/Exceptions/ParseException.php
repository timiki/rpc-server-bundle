<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Exceptions;

/**
 * Json parse exception.
 */
class ParseException extends ErrorException
{
    public function __construct()
    {
        parent::__construct('Parse error', -32700);
    }
}

