<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Mapper;

class MetaData
{
    public function __construct(private readonly array $data = [])
    {
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    public function getAll(): array
    {
        return $this->data;
    }
}
