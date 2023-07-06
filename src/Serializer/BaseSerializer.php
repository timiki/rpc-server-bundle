<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class BaseSerializer implements SerializerInterface
{
    public function __construct(private readonly SymfonySerializerInterface $serializer)
    {
    }

    public function serialize(mixed $jsonResponse): string
    {
        return $this->serializer->serialize($jsonResponse, 'json');
    }
}
