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
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            JsonExecuteEvent::EVENT => ['execute', 4096],
        ];
    }

    /**
     * ValidatorSubscriber constructor.
     *
     * @param ValidatorInterface|null $validator
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
        if ($this->validator) {

            $result = $this->validator->validate($event->getObject());

            if ($result->count() > 0) {

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
    }
}