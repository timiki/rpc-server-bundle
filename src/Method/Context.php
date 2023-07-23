<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Method;

use Timiki\Bundle\RpcServerBundle\Mapper\MetaData;
use Timiki\RpcCommon\JsonRequest;

class Context
{
    public function __construct(
        private readonly MetaData $metaData,
        private readonly JsonRequest $jsonRequest,
    ) {
    }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this
            ->getJsonRequest()
            ->get(
                $key,
                $this->getMetaData()->get('params', [])[$key] ?? $default
            );
    }

    public function hasParam(string $key): bool
    {
        return isset($this->getMetaData()->get('params', [])[$key]);
    }

    public function getParams(): array
    {
        $params = $this->getMetaData()->get('params', []);

        array_walk($params, function (&$value, $key) {
            $value = $this->getJsonRequest()->get($key, $value);
        });

        return $params;
    }

    public function getMetaData(): MetaData
    {
        return $this->metaData;
    }

    public function getJsonRequest(): JsonRequest
    {
        return $this->jsonRequest;
    }
}
