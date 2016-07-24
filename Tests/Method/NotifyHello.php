<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("notify_hello")
 */
class NotifyHello
{
    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return 'Hello';
    }
}
