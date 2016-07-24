<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;
use Timiki\Bundle\RpcServerBundle\Server\Exceptions\MethodException;

/**
 * @Rpc\Method("get_error")
 */
class GetError
{
    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        throw new MethodException('Exception data');
    }
}
