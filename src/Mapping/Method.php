<?php

namespace Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
final class Method
{
    /**
     * @var string
     */
    public $value;
}
