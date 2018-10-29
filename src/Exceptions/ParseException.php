<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

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
