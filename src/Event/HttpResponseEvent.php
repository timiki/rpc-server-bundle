<?php

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\EventDispatcher\Event;
use Timiki\RpcCommon\JsonResponse;

class HttpResponseEvent extends Event
{
    /**
     * @var HttpResponse
     */
    private $httpResponse;

    /**
     * @var JsonResponse|JsonResponse[]
     */
    private $jsonResponse;

    /**
     * HttpResponseEvent constructor.
     *
     * @param HttpResponse                $httpResponse
     * @param JsonResponse|JsonResponse[] $jsonResponse
     */
    public function __construct(HttpResponse $httpResponse, JsonResponse $jsonResponse)
    {
        $this->httpResponse = $httpResponse;
        $this->jsonResponse = $jsonResponse;
    }

    /**
     * Get http response.
     *
     * @return HttpResponse
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * @return JsonResponse|JsonResponse[]
     */
    public function getJsonResponse()
    {
        return $this->jsonResponse;
    }
}
