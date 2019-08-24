<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\RpcCommon\JsonRequest;

class JsonRequestEvent extends Event
{
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
