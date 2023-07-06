<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonRequestEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\MethodNotGrantedException;

class AuthorizationCheckerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface|null $authChecker = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            JsonRequestEvent::class => ['onRequest', 4096],
        ];
    }

    public function onRequest(JsonRequestEvent $event): void
    {
        if (null === $this->authChecker) {
            return;
        }

        $mapper = $event->getMapper();
        $request = $event->getJsonRequest();
        $meta = $mapper->getMetaData($request->getMethod());

        if (empty($meta->get('roles'))) {
            return;
        }

        $isGranted = false;

        foreach ($meta->get('roles') as $role) {
            if ($this->authChecker->isGranted($role)) {
                $isGranted = true;
            }
        }

        if (!$isGranted) {
            throw new MethodNotGrantedException();
        }
    }
}
