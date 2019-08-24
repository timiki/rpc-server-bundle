<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{
    /**
     * Event dispatcher.
     *
     * @var null|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Get event dispatcher.
     *
     * @return null|EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Set event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param Event $event
     *
     * @return object
     */
    public function dispatch(Event $event = null)
    {
        if ($this->eventDispatcher) {
            return $this->eventDispatcher->dispatch($event);
        }
    }
}
