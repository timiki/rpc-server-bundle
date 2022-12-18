<?php

namespace Timiki\Bundle\RpcServerBundle\Serializer;

interface SerializerInterface
{
    /**
     * Serialize data.
     */
    public function serialize(mixed $data): mixed;
}
