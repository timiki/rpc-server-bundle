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
    private $meta;

    /**
     * JsonExecuteEvent constructor.
     *
     * @param $object
     * @param array $meta
     */
    public function __construct($object, array $meta = [])
    {
        $this->object = $object;
        $this->meta   = $meta;
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
     * Get meta.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}