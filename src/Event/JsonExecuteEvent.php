<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodMetaData;
use Timiki\RpcCommon\JsonRequest;

class JsonExecuteEvent extends Event
{
    public const EVENT = 'rpc.server.json.execute';

    /**
     * @var object
     */
    private $object;

    /**
     * @var MethodMetaData
     */
    private $metadata;

    /**
     * @var null|\ReflectionObject
     */
    private $objectReflection;

    /**
     * @var JsonRequest
     */
    private $jsonRequest;

    /**
     * JsonExecuteEvent constructor.
     *
     * @param $object
     * @param MethodMetaData $metadata
     * @param JsonRequest    $jsonRequest
     */
    public function __construct($object, MethodMetaData $metadata, JsonRequest $jsonRequest)
    {
        $this->object      = $object;
        $this->metadata    = $metadata;
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
     *
     * @return array
     */
    public function getMetadata(): MethodMetaData
    {
        return $this->metadata;
    }

    /**
     * @return JsonRequest
     */
    public function getJsonRequest(): JsonRequest
    {
        return $this->jsonRequest;
    }

    /**
     * @return \ReflectionObject
     */
    public function getObjectReflection(): \ReflectionObject
    {
        if (null !== $this->objectReflection) {
            return $this->objectReflection;
        }

        return $this->objectReflection = new \ReflectionObject($this->object);
    }
}
