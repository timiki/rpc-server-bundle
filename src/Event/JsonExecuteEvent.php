<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\Mapper\MapperInterface;
use Timiki\Bundle\RpcServerBundle\Mapper\MetaData;
use Timiki\RpcCommon\JsonRequest;

class JsonExecuteEvent extends Event
{
    public function __construct(
        private readonly object $object,
        private readonly MetaData $metadata,
        private readonly MapperInterface $mapper,
        private readonly JsonRequest $jsonRequest,
        private mixed $result
    ) {
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getMetadata(): MetaData
    {
        return $this->metadata;
    }

    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }

    public function getJsonRequest(): JsonRequest
    {
        return $this->jsonRequest;
    }

    public function getObjectReflection(): \ReflectionObject
    {
        return new \ReflectionObject($this->object);
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): void
    {
        $this->stopPropagation();
        $this->result = $result;
    }
}
