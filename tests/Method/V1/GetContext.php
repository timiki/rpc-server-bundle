<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Tests\Timiki\Bundle\RpcServerBundle\Method\AbstractMethod;
use Timiki\Bundle\RpcServerBundle\Attribute as RPC;
use Timiki\Bundle\RpcServerBundle\Method\Context;

#[RPC\Method('get_context')]
class GetContext extends AbstractMethod
{
    #[RPC\Param]
    protected $a = 1;

    #[RPC\Param]
    protected $b;

    public function __invoke(Context $context): array
    {
        return $context->getParams();
    }
}
