<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Component\Stopwatch\Stopwatch;

trait StopwatchTrait
{
    /**
     * @var null|Stopwatch
     */
    protected $stopwatch;

    /**
     * Get stop watch.
     *
     * @return null|Stopwatch
     */
    public function getStopwatch()
    {
        return $this->stopwatch;
    }

    /**
     * Set stop watch.
     *
     * @param null|Stopwatch $stopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }
}
