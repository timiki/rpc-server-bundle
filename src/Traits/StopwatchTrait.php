<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\Stopwatch\Stopwatch;

trait StopwatchTrait
{
    /**
     * @var Stopwatch|null
     */
    protected $stopwatch;

    /**
     * Get stop watch.
     *
     * @return Stopwatch|null
     */
    public function getStopwatch()
    {
        return $this->stopwatch;
    }

    /**
     * Set stop watch.
     */
    public function setStopwatch(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }
}
