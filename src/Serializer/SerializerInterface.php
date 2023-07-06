<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $jsonResponse): string;
}
