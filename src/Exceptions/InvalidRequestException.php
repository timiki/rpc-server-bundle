<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class InvalidRequestException extends ErrorException
{
    public function __construct(mixed $data = null, string|int|float $id = null)
    {
        parent::__construct('Invalid Request', -32600, $data, $id);
    }
}
