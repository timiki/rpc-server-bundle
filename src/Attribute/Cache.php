<?php

namespace Timiki\Bundle\RpcServerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Cache
{
    public ?int $lifetime;

    public function __construct(?int $lifetime)
    {
        $this->lifetime = $lifetime;
    }
}
