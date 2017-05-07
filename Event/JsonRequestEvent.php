<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\JsonRequest;

/**
 * JsonRequest event
 */
class JsonRequestEvent extends Event
{
    const EVENT = 'json.request';

    /**
     * @var JsonRequest
     */
    private $jsonRequest;

    /**
     * JsonRequestEvent constructor.
     *
     * @param JsonRequest $jsonResponse
     */
    public function __construct(JsonRequest $jsonResponse)
    {
        $this->jsonRequest = $jsonResponse;
    }

    /**
     * Get json request.
     *
     * @return JsonRequest
     */
    public function getJsonRequest()
    {
        return $this->jsonRequest;
    }
}