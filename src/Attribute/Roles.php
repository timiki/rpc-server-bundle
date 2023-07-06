<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Roles
{
    /**
     * @param array<string> $roles
     */
    public function __construct(public array $roles = [])
    {
    }
}
