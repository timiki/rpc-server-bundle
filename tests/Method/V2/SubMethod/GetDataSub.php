<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1\SubMethod;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('get_data_sub')]
class GetDataSub
{
    #[RPC\Param]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    protected $a;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(): array
    {
        return ['hello', 5];
    }
}
