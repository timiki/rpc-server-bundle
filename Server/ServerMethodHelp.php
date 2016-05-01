<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Timiki\Bundle\RpcServerBundle\Server\Method;
use Timiki\Bundle\RpcServerBundle\Method\Result;

class ServerMethodHelp extends Method
{
    /**
     * Get the method description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return 'Return help for method';
    }

    /**
     * Get the method params.
     *
     * @return array
     */
    public function getParams()
    {
        return array(
            ['method', 'required']
        );
    }

    /**
     * Execute the server method.
     *
     * @param Result $result
     * @param string $method
     * @return array
     */
    public function execute(Result $result, $method)
    {
        if (!$method = $this->getServer()->getMethod($method)) {
            $result->setResult('Unknown method');
        }
        $result->setResult([
            'method'      => $method->getName(),
            'description' => $method->getDescription(),
            'params'      => $method->getParams()
        ]);
    }
}