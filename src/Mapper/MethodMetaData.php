<?php

namespace Timiki\Bundle\RpcServerBundle\Mapper;

class MethodMetaData
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $execute;

    /**
     * @var array
     */
    private $params;

    /**
     * @var int|null
     */
    private $cache;

    /**
     * @var array
     */
    private $roles;

    /**
     * @param null $cache
     */
    public function __construct(string $method, string $execute, array $params = [], $cache = null, array $roles = null)
    {
        $this->method = $method;
        $this->execute = $execute;
        $this->params = $params;
        $this->cache = $cache;
        $this->roles = $roles;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getExecute(): string
    {
        return $this->execute;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return int|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return array
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }
}
