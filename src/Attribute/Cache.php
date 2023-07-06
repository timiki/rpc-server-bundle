<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Cache
{
    public function __construct(public int $lifetime = 0)
    {
    }
}
