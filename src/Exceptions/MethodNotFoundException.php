<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class MethodNotFoundException extends ErrorException
{
    public function __construct(mixed $data = null, int|string $id = null)
    {
        parent::__construct('Method not found', -32601, $data, $id);
    }
}
