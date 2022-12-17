<?php

namespace Timiki\Bundle\RpcServerBundle\Attribute;

use Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Roles
{
    /**
     * @var array<string>
     */
    public ?array $roles = [];

    public function __construct(?array $roles = [])
    {
        $this->roles = $roles;
    }
}
