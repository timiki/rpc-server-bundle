<?php

namespace Timiki\Bundle\RpcServerBundle\Mapper;

class Mapper
{
    /**
     * @var array
     */
    private $methodsMetaData = [];

    /**
     * @var array
     */
    private $dirtyMethods = [];

    public function addMethods(array $methodsMetaData): void
    {
        $this->dirtyMethods = $methodsMetaData;
    }

    public function hasMethod(string $name): bool
    {
        return isset($this->dirtyMethods[$name]);
    }

    /**
     * @return \Timiki\Bundle\RpcServerBundle\Mapper\MethodMetaData|null
     */
    public function getMethod(string $name): ?MethodMetaData
    {
        if (true === isset($this->methodsMetaData[$name])) {
            return $this->methodsMetaData[$name];
        }

        if (false === $this->hasMethod($name)) {
            return null;
        }

        return $this->methodsMetaData[$name] = new MethodMetaData(...$this->dirtyMethods[$name]);
    }
}
