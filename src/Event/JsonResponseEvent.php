<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Timiki\Bundle\RpcServerBundle\Mapper\MapperInterface;
use Timiki\RpcCommon\JsonResponse;

class JsonResponseEvent extends Event
{
    public function __construct(
        private readonly JsonResponse $jsonResponse,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function getJsonResponse(): JsonResponse
    {
        return $this->jsonResponse;
    }

    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }
}
