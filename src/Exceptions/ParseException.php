<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class ParseException extends ErrorException
{
    public function __construct()
    {
        parent::__construct('Parse error', -32700);
    }
}
