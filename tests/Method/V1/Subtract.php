<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("subtract")
 */
class Subtract
{
    /**
     * @Rpc\Param
     */
    protected $subtrahend;

    /**
     * @Rpc\Param
     */
    protected $minuend;

    /**
     * @Rpc\Execute
     */
    public function execute()
    {
        return $this->subtrahend - $this->minuend;
    }
}
