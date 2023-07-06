<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('subtract')]
class Subtract
{
    #[RPC\Param]
    protected $subtrahend;

    #[RPC\Param]
    protected $minuend;

    public function __invoke()
    {
        return $this->subtrahend - $this->minuend;
    }
}
