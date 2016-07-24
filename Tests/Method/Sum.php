<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Mapping as Rpc;

/**
 * @Rpc\Method("sum")
 */
class Sum
{
    /**
     * @Rpc\Param()
     */
    protected $a;

    /**
     * @Rpc\Param()
     */
    protected $b;

    /**
     * @Rpc\Param()
     */
    protected $c;

    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return $this->a + $this->b + $this->c;
    }
}
