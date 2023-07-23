<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\Stopwatch\Stopwatch;

trait StopwatchTrait
{
    protected Stopwatch|null $stopwatch = null;

    public function getStopwatch(): Stopwatch|null
    {
        return $this->stopwatch;
    }

    public function setStopwatch(Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }
}
