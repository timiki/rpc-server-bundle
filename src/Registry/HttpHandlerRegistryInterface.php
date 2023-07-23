<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\Registry;

use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandlerInterface;

interface HttpHandlerRegistryInterface
{
    public function add(string $name, HttpHandler $httpHandler): void;

    public function get(string $name): HttpHandlerInterface;

    public function has(string $name): bool;
}
