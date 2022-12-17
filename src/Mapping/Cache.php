<?php

namespace Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
final class Cache
{
    /**
     * @var int
     */
    public $lifetime;
}
