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
     * @var int
     */
    private $cache;

    /**
     * @var array
     */
    private $roles;

    public function __construct(string $method, string $execute, array $params = [], $cache = null, array $roles = null)
    {
        $this->method  = $method;
        $this->execute = $execute;
        $this->params  = $params;
        $this->cache   = $cache;
        $this->roles   = $roles;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getExecute(): string
    {
        return $this->execute;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return int
     */
    public function getCache(): ?int
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
