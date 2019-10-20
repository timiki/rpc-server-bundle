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

    /**
     * @param array $methodsMetaData
     */
    public function addMethods(array $methodsMetaData): void
    {
        $this->dirtyMethods = $methodsMetaData;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasMethod(string $name): bool
    {
        return isset($this->dirtyMethods[$name]);
    }

    /**
     * @param string $name
     *
     * @return null|\Timiki\Bundle\RpcServerBundle\Mapper\MethodMetaData
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
