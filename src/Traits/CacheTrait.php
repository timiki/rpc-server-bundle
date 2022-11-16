<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\Cache\Adapter\AdapterInterface;

trait CacheTrait
{
    protected ?AdapterInterface $cache = null;

    public function setCache(AdapterInterface $cache = null)
    {
        $this->cache = $cache;
    }

    public function getCache(): ?AdapterInterface
    {
        return $this->cache;
    }
}
