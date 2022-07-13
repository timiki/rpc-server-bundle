<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\RpcCommon\JsonResponse;

class JsonResponseEvent extends Event
{
    /**
     * @var JsonResponse
     */
    private $jsonResponse;

    /**
     * JsonResponseEvent constructor.
     */
    public function __construct(JsonResponse $jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;
    }

    /**
     * Get json response.
     *
     * @return JsonResponse
     */
    public function getJsonResponse()
    {
        return $this->jsonResponse;
    }
}
