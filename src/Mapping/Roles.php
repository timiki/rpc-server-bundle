<?php

namespace Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Roles
{
    /**
     * @var array<string>
     */
    public $value = [];
}
