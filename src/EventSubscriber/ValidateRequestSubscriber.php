<?php

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonRequestEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidRequestException;

class ValidateRequestSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            JsonRequestEvent::class => ['onRequest', 4096],
        ];
    }

    public function onRequest(JsonRequestEvent $event)
    {
        $jsonRequest = $event->getJsonRequest();

        if (empty($jsonRequest->getMethod()) || (!empty($jsonRequest->getParams()) && !\is_array($jsonRequest->getParams()))) {
            throw new InvalidRequestException();
        }
    }
}
