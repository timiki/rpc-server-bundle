<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;
use Timiki\Bundle\RpcServerBundle\Exceptions\MethodException;

#[RPC\Method('get_error')]
class GetError
{
    public function __invoke()
    {
        throw new MethodException('Exception', 'Data');
    }
}
