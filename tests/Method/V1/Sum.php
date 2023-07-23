<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('sum')]
class Sum
{
    #[RPC\Param]
    protected $a;

    #[RPC\Param]
    protected $b;

    #[RPC\Param]
    protected $c;

    public function __invoke()
    {
        return $this->a + $this->b + $this->c;
    }
}
