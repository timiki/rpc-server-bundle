<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("get_data")
 */
class GetData
{
    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return ["hello", 5];
    }
}
