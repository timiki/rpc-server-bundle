<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('update')]
class Update
{
    public function __invoke()
    {
        // Notification
    }
}
