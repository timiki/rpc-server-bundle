<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Method
{
    public function __construct(public string $name)
    {
    }
}
