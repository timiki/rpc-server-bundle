<?php

namespace Timiki\Bundle\RpcServerBundle\Serializer;

interface SerializerInterface
{
    /**
     * Serialize data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function serialize($data);
}
