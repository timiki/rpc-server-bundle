<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonPreExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidParamsException;

class ValidatorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ValidatorInterface|null $validator = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            JsonPreExecuteEvent::class => ['execute', 1024], // run after auth check
        ];
    }

    public function execute(JsonPreExecuteEvent $event): void
    {
        if (null === $this->validator) {
            return;
        }

        $result = $this->validator->validate($event->getObject());

        if (0 === $result->count()) {
            return;
        }

        $data = [];

        /* @var ConstraintViolation $constraintViolation */
        foreach ($result as $constraintViolation) {
            $name = $constraintViolation->getPropertyPath() ? $constraintViolation->getPropertyPath() : 'violations';

            if (!isset($data[$name])) {
                $data[$name] = [];
            }

            $data[$name][] = $constraintViolation->getMessage();
        }

        throw new InvalidParamsException($data);
    }
}
