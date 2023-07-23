<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Handler;

use Timiki\RpcCommon\JsonRequest;
use Timiki\RpcCommon\JsonResponse;

interface JsonHandlerInterface
{
    public function handleJsonRequest(JsonRequest $jsonRequest): JsonResponse;
}
