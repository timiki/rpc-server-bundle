<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\HttpKernel\Profiler\Profiler;

trait ProfilerTrait
{
    protected ?Profiler $profiler = null;

    public function getProfiler(): ?Profiler
    {
        return $this->profiler;
    }

    public function setProfiler(?Profiler $profiler = null): void
    {
        $this->profiler = $profiler;
    }
}
