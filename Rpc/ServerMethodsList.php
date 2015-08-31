<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Timiki\Bundle\RpcServerBundle\Server\Method;
use Timiki\Bundle\RpcServerBundle\Method\Result;

class ServerMethodsList extends Method
{
    /**
     * Get the method description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return 'Return the method list on server';
    }

    /**
     * Execute the server method
     * @param Result $result
     */
    public function execute(Result $result)
    {
        $result->setResult($this->getServer()->getMethods());
    }
}