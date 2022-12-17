<?php

namespace Timiki\Bundle\RpcServerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Method
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
