<?php

namespace Timiki\Bundle\RpcServerBundle\Serializer;

interface SerializerInterface
{
    /**
     * Serialize data.
     *
     * @param mixed $data
     * @return array
     */
    public function serialize($data);
}
