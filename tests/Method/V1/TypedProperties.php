<?php

declare(strict_types=1);

namespace Tests\Timiki\Bundle\RpcServerBundle\Method\V1;

use Tests\Timiki\Bundle\RpcServerBundle\Method\AbstractMethod;
use Timiki\Bundle\RpcServerBundle\Attribute as RPC;

#[RPC\Method('typed_properties')]
class TypedProperties extends AbstractMethod
{
    #[RPC\Param]
    protected int $int;

    #[RPC\Param]
    protected float $float;

    #[RPC\Param]
    protected bool $bool;

    #[RPC\Param]
    protected array $array;

    #[RPC\Param]
    protected string $string;

    #[RPC\Param]
    protected ?string $nullableString;

    #[RPC\Param]
    protected string|int|array $multiType;

    #[RPC\Param]
    protected bool|float|null $nullableMultiType;

    public function __invoke(): array
    {
        return [
            $this->int,
            $this->float,
            $this->bool,
            $this->array,
            $this->string,
            $this->nullableString,
            $this->multiType,
            $this->nullableMultiType,
        ];
    }
}
