<?php

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonRequestEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidRequestException;

/**
 * ValidateRequestSubscriber
 */
class ValidateRequestSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            JsonRequestEvent::EVENT => ['onRequest', 4096],
        ];
    }

    /**
     * @param JsonRequestEvent $event
     */
    public function onRequest(JsonRequestEvent $event)
    {
        $jsonRequest = $event->getJsonRequest();

        if (empty($jsonRequest->getMethod()) || (!empty($jsonRequest->getParams()) && !is_array($jsonRequest->getParams()))) {
            throw new InvalidRequestException();
        }
    }
}