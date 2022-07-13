<?php

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{
    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher;

    /**
     * Get event dispatcher.
     *
     * @return EventDispatcherInterface|null
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
     */
    public function dispatch(Event $event = null): ?object
    {
        return $this->eventDispatcher->dispatch($event);
    }
}
