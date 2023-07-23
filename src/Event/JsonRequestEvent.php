<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\Mapper\MapperInterface;
use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

class JsonRequestEvent extends Event
{
    private null|JsonResponse $jsonResponse = null;

    public function __construct(
        private readonly JsonRequest $jsonRequest,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function getJsonRequest(): JsonRequest
    {
        return $this->jsonRequest;
    }

    public function setJsonResponse(JsonResponse|null $jsonResponse): void
    {
        $this->jsonResponse = $jsonResponse;
    }

    public function getJsonResponse(): JsonResponse|null
    {
        return $this->jsonResponse;
    }

    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }
}
