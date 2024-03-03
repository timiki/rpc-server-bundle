<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\Stopwatch\Stopwatch;

trait StopwatchTrait
{
    protected ?Stopwatch $stopwatch = null;

    public function getStopwatch(): ?Stopwatch
    {
        return $this->stopwatch;
    }

    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }
}
