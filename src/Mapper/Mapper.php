<?php

namespace Timiki\Bundle\RpcServerBundle\Mapper;

class Mapper
{
    private $methods = [];

    public function addMethod(string $methodName, array $methodMeta): void
    {
        $this->methods[$methodName] = new MethodMetaData(...$methodMeta);
    }

    public function hasMethod(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    public function getMethod(string $name): ?MethodMetaData
    {
        return $this->methods[$name];
    }
}
