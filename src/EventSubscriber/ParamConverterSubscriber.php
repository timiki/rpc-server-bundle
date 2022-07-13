<?php

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonPreExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions;

class ParamConverterSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            JsonPreExecuteEvent::class => ['convert', 2048],
        ];
    }

    public function convert(JsonPreExecuteEvent $event)
    {
        $jsonRequest = $event->getJsonRequest();
        $methodMetaData = $event->getMetadata();

        // Get params
        if (\array_keys($jsonRequest->getParams()) === \range(0, \count($jsonRequest->getParams()) - 1)) {
            // Given only values
            $values = $jsonRequest->getParams();  // Given only values
            $params = [];

            foreach (\array_keys($methodMetaData->getParams()) as $id => $key) {
                if (isset($values[$id])) {
                    $params[$key] = $values[$id];
                }
            }
        } else {
            // Given name => value
            $params = $jsonRequest->getParams();
        }

        // Inject params
        $reflection = $event->getObjectReflection();
        $methodHandler = $event->getObject();

        foreach ($params as $name => $value) {
            if (!$reflection->hasProperty($name)) {
                throw new Exceptions\InvalidParamsException(null, $jsonRequest->getId());
            }

            $reflectionProperty = $reflection->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($methodHandler, $value);
        }
    }
}
