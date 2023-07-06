<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonPreExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions;

class ParamConverterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            JsonPreExecuteEvent::class => ['convert', 2048],
        ];
    }

    public function convert(JsonPreExecuteEvent $event): void
    {
        $jsonRequest = $event->getJsonRequest();
        $methodMetaData = $event->getMetadata();

        // Get params
        if (\array_keys($jsonRequest->getParams()) === \range(0, \count($jsonRequest->getParams()) - 1)) {
            // Given only values
            $values = $jsonRequest->getParams();
            $params = [];

            foreach (\array_keys($methodMetaData->get('params')) as $id => $key) {
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
                if ($this->parameterBag->get('rpc.server.parameters.allow_extra_params')) {
                    continue;
                }

                throw new Exceptions\InvalidParamsException(null, $jsonRequest->getId());
            }

            $reflectionProperty = $reflection->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($methodHandler, $value);
        }
    }
}
