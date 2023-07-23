<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Registry;

use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandlerInterface;

class HttpHandlerRegistry implements HttpHandlerRegistryInterface
{
    /**
     * @var array<HttpHandler>
     */
    private array $httpHandlers = [];

    public function add(string $name, HttpHandler $httpHandler): void
    {
        $this->httpHandlers[$name] = $httpHandler;
    }

    public function get(string $name): HttpHandlerInterface
    {
        if (!isset($this->httpHandlers[$name])) {
            throw new \Exception("HttpHandler {$name} not found");
        }

        return $this->httpHandlers[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->httpHandlers[$name]);
    }
}
