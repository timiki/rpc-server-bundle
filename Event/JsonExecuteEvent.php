<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * JsonExecuteEvent event
 */
class JsonExecuteEvent extends Event
{
    const EVENT = 'json.execute';

    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $metadata;

    /**
     * JsonExecuteEvent constructor.
     *
     * @param $object
     * @param array $metadata
     */
    public function __construct($object, array $metadata = [])
    {
        $this->object   = $object;
        $this->metadata = $metadata;
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
    public function getMetadata()
    {
        return $this->metadata;
    }
}