<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method;

use Timiki\Bundle\RpcServerBundle\Exceptions\MethodException;
use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("get_error")
 */
class GetError
{
    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        throw new MethodException('Exception data');
    }
}
