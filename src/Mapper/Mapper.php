<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Mapper;

use Timiki\Bundle\RpcServerBundle\Exceptions\MethodNotFoundException;

class Mapper implements MapperInterface
{
    private array $methods = [];

    public function addMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    public function hasMethod(string $name): bool
    {
        return array_key_exists($name, $this->methods);
    }

    public function getMetaData(string $name): MetaData
    {
        if (!$this->hasMethod($name)) {
            throw new MethodNotFoundException($name);
        }

        return new MetaData((array) $this->methods[$name]);
    }

    public function getHash(): string
    {
        return md5(serialize($this->methods));
    }
}
