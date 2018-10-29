<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Cache trait.
 */
trait CacheTrait
{
    /**
     * @var CacheProvider
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
     * @return CacheProvider
     */
    public function getCache()
    {
        return $this->cache;
    }
}
