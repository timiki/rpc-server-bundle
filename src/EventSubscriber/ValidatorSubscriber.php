<?php

namespace Timiki\Bundle\RpcServerBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Timiki\Bundle\RpcServerBundle\Event\JsonExecuteEvent;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidParamsException;

class ValidatorSubscriber implements EventSubscriberInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JsonExecuteEvent::EVENT => ['execute', 1024], // run after auth check
        ];
    }

    /**
     * ValidatorSubscriber constructor.
     *
     * @param null|ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator;
    }

    /**
     * @param JsonExecuteEvent $event
     */
    public function execute(JsonExecuteEvent $event)
    {
        if (null === $this->validator) {
            return;
        }

        $result = $this->validator->validate($event->getObject());

        if (0 === $result->count()) {
            return;
        }

        $data = [];

        // @var ConstraintViolation $constraintViolation
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
