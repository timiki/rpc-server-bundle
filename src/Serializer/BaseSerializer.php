<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class BaseSerializer implements SerializerInterface
{
    public function __construct(private readonly SymfonySerializerInterface $serializer)
    {
    }

    public function serialize(mixed $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    public function toArray(mixed $data): array
    {
        return json_decode($this->serialize($data));
    }
}
