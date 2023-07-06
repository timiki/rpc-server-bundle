<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1\SubMethod\SubSubDir;

use Symfony\Component\Validator\Constraints as Assert;
use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('get_data_sub_sub')]
class GetDataSubSub
{
    #[RPC\Param]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    protected $a;

    public function __invoke(): array
    {
        return ['hello', 5];
    }
}
