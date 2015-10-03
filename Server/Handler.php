<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\Bundle\RpcServerBundle\RpcServer;
use Timiki\Bundle\RpcServerBundle\Method\Result;

/**
 * Abstract server handler
 */
abstract class Handler
{
    /**
     * Server instance
     *
     * @var RpcServer
     */
    private $server;

    /**
     * Handler name
     *
     * @var string
     */
    private $name;

    /**
     * Set server instance
     *
     * @param $server
     * @return $this
     */
    public function setServer(&$server)
    {
        if ($server instanceof RpcServer) {
            $this->server = $server;
        }

        return $this;
    }

    /**
     * Get handler name
     *
     * @return string
     */
    public function getName()
    {
        return strtolower($this->name);
    }

    /**
     * Get server instance
     *
     * @return RpcServer|null
     */
    public function &getServer()
    {
        return $this->server;
    }

    /**
     * Process httpRequest for get method name
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return string
     */
    public function getHttpRequestMethod(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
    }

    /**
     * Process httpRequest for get method params
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return array
     */
    public function getHttpRequestParams(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
    }

    /**
     * Process httpRequest for get method extra
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return array
     */
    public function getHttpRequestExtra(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
    }

    /**
     * Process result
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @param \Symfony\Component\HttpFoundation\Response $httpResponse
     * @param Result $result
     */
    public function processResult(\Symfony\Component\HttpFoundation\Request &$httpRequest, \Symfony\Component\HttpFoundation\Response &$httpResponse, Result &$result)
    {
    }
}
