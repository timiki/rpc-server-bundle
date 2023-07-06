<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Symfony\Component\Validator\Constraints as Assert;
use Tests\Timiki\Bundle\RpcServerBundle\Method\AbstractMethod;
use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('get_data')]
#[RPC\Roles(['Some_Role'])]
#[RPC\Cache(10)]
class GetData extends AbstractMethod
{
    #[RPC\Param]
    #[Assert\NotBlank()]
    #[Assert\Type(type: 'numeric')]
    protected $a;

    public function __invoke(): array
    {
        return ['hello', 5];
    }
}
