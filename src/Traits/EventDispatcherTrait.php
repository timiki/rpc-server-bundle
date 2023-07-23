<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Traits;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait EventDispatcherTrait
{
    protected EventDispatcherInterface|null $eventDispatcher = null;

    public function getEventDispatcher(): EventDispatcherInterface|null
    {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(Event $event = null): object
    {
        return $this->eventDispatcher->dispatch($event);
    }
}
