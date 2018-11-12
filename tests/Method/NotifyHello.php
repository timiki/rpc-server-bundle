<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("notify_hello")
 */
class NotifyHello
{
    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        return 'Hello';
    }
}
