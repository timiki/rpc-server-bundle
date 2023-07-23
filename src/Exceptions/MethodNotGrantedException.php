<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

class MethodNotGrantedException extends ErrorException
{
    public function __construct(mixed $data = null, int|string $id = null)
    {
        parent::__construct('Method not granted', -32001, $data, $id);
    }
}
