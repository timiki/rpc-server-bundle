<?php

namespace Timiki\Bundle\RpcServerBundle\Registry;

use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;

class HttpHandlerRegistry
{
    /**
     * Http handlers.
     *
     * @var HttpHandler[]
     */
    private $httpHandlers = [];

    /**
     * @param string $name
     */
    public function add($name, HttpHandler $httpHandler)
    {
        $this->httpHandlers[$name] = $httpHandler;
    }

    /**
     * Ger http handler by name.
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return HttpHandler
     */
    public function get($name)
    {
        if (!isset($this->httpHandlers[$name])) {
            throw new \Exception("HttpHandler {$name} not found");
        }

        return $this->httpHandlers[$name];
    }

    /**
     * Is http handler exist.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->httpHandlers[$name]);
    }
}
