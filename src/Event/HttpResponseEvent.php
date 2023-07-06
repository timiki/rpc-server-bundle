<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Contracts\EventDispatcher\Event;
use Timiki\RpcCommon\JsonResponse;

class HttpResponseEvent extends Event
{
    /**
     * @param JsonResponse|JsonResponse[]|null $jsonResponse
     */
    public function __construct(private readonly HttpResponse $httpResponse, private readonly JsonResponse|array|null $jsonResponse = null)
    {
    }

    public function getHttpResponse(): HttpResponse
    {
        return $this->httpResponse;
    }

    /**
     * @return JsonResponse|JsonResponse[]|null
     */
    public function getJsonResponse(): JsonResponse|array|null
    {
        return $this->jsonResponse;
    }
}
