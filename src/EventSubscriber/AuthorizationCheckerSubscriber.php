<?php

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonPreExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\MethodNotGrantedException;

class AuthorizationCheckerSubscriber implements EventSubscriberInterface
{
    /**
     * @var null|AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JsonPreExecuteEvent::class => ['execute', 4096],
        ];
    }

    /**
     * AuthorizationCheckerSubscriber constructor.
     *
     * @param null|AuthorizationCheckerInterface $authChecker
     */
    public function __construct(AuthorizationCheckerInterface $authChecker = null)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @param JsonPreExecuteEvent $event
     */
    public function execute(JsonPreExecuteEvent $event)
    {
        $methodMetaData = $event->getMetadata();

        if (!$this->authChecker || null === $methodMetaData->getRoles()) {
            return;
        }

        if (!$this->authChecker->isGranted($methodMetaData->getRoles())) {
            throw new MethodNotGrantedException();
        }
    }
}
