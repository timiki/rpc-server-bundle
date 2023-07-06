<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class InvalidParamsException extends ErrorException
{
    public function __construct(mixed $data = null, int|string $id = null)
    {
        parent::__construct('Invalid params', -32602, $data, $id);
    }
}
