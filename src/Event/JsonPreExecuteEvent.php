<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodMetaData;
use Timiki\RpcCommon\JsonRequest;

class JsonPreExecuteEvent extends Event
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var MethodMetaData
     */
    private $metadata;

    /**
     * @var \ReflectionObject|null
     */
    private $objectReflection;

    /**
     * @var JsonRequest
     */
    private $jsonRequest;

    /**
     * JsonExecuteEvent constructor.
     *
     * @param object $object
     */
    public function __construct($object, MethodMetaData $metadata, JsonRequest $jsonRequest)
    {
        $this->object = $object;
        $this->metadata = $metadata;
        $this->jsonRequest = $jsonRequest;
    }

    /**
     * Get object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get metadata.
     */
    public function getMetadata(): MethodMetaData
    {
        return $this->metadata;
    }

    public function getJsonRequest(): JsonRequest
    {
        return $this->jsonRequest;
    }

    public function getObjectReflection(): \ReflectionObject
    {
        if (null !== $this->objectReflection) {
            return $this->objectReflection;
        }

        return $this->objectReflection = new \ReflectionObject($this->object);
    }
}
