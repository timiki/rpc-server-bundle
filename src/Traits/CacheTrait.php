<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Doctrine\Common\Cache\CacheProvider;

trait CacheTrait
{
    /**
     * @var CacheProvider|null
     */
    protected $cache;

    /**
     * Set cache.
     *
     * @param CacheProvider $cache Cache provider
     */
    public function setCache(CacheProvider $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Get cache.
     *
     * @return CacheProvider|null
     */
    public function getCache()
    {
        return $this->cache;
    }
}
