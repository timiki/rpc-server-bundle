<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('notify_hello')]
class NotifyHello
{
    public function __invoke(): string
    {
        return 'Hello';
    }
}
