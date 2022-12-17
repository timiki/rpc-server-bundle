<?php

namespace Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * @Annotation
 *
 * @Target({"CLASS", "PROPERTY"})
 */
final class Roles
{
    /**
     * @var array<string>
     */
    public $value = [];
}
