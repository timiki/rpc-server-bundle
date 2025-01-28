<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonPreExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidParamsException;

class ParamConverterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly ?ValidatorInterface $validator = null,
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

        if (null !== $this->validator) {
            $errors = [];
            foreach ($params as $name => $value) {
                if (!$reflection->hasProperty($name)) {
                    if ($this->parameterBag->get('rpc.server.parameters.allow_extra_params')) {
                        continue;
                    }

                    throw new InvalidParamsException(null, $jsonRequest->getId());
                }

                $reflectionProperty = $reflection->getProperty($name);
                $reflectionProperty->setAccessible(true);

                $typeErrors = $this->checkTypes($reflectionProperty->getType(), $value);
                if (null === $typeErrors) {
                    continue;
                }

                $errors[$reflectionProperty->getName()] = $typeErrors;
            }

            if (0 < \count($errors)) {
                throw new InvalidParamsException($errors);
            }
        }

        foreach ($params as $name => $value) {
            $reflectionProperty = $reflection->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($methodHandler, $value);
        }
    }

    private function checkTypes(?\ReflectionType $reflectionType, mixed $value): ?array
    {
        if (null === $reflectionType) {
            return null;
        }

        $constraints = [];
        if (!$reflectionType->allowsNull()) {
            $constraints[] = new NotNull();
        }

        $types = [];
        if ($reflectionType instanceof \ReflectionUnionType) {
            $types = array_map(fn ($type) => $type->getName(), $reflectionType->getTypes());
        } else {
            $types[] = $reflectionType->getName();
        }

        if (\count($types) > 0) {
            $constraints[] = new Type($types);
        }

        $result = $this->validator->validate($value, $constraints);

        if (0 === $result->count()) {
            return null;
        }

        /* @var ConstraintViolation $constraintViolation */
        return array_map(fn ($constraintViolation) => $constraintViolation->getMessage(), iterator_to_array($result));
    }
}
