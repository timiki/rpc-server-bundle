<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class MethodException extends ErrorException
{
    public function __construct(string $message = 'Method exception', mixed $data = null, int $code = -32002)
    {
        parent::__construct($message, $code, $data);
    }
}
