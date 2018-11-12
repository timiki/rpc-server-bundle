<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\HttpKernel\Profiler\Profiler;

trait ProfilerTrait
{
    /**
     * Profiler.
     *
     * @var null|Profiler
     */
    protected $profiler;

    /**
     * Get profiler.
     *
     * @return null|Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * Set Profiler.
     *
     * @param Profiler $profiler
     */
    public function setProfiler(Profiler $profiler = null)
    {
        $this->profiler = $profiler;
    }
}
