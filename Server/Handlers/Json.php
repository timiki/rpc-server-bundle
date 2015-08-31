<?php

namespace Timiki\Bundle\RpcServerBundle\Server\Handlers;

use Timiki\Bundle\RpcServerBundle\Server\Handler;
use Timiki\Bundle\RpcServerBundle\Method\Result;

class Json extends Handler
{
    /**
     * Check if request is valid
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return Array
     */
    protected function parserRequest(\Symfony\Component\HttpFoundation\Request $httpRequest)
    {
        $request = [];

        try {
            $jsonRequest = json_decode($httpRequest->getContent(), true);
        } catch (\Exception $e) {
            return $request;
        }

        if (array_key_exists('jsonrpc', $jsonRequest)) {
            $request['jsonrpc'] = $jsonRequest['jsonrpc'];
        } else {
            $request['jsonrpc'] = null;
        }

        if (array_key_exists('method', $jsonRequest)) {
            $request['method'] = $jsonRequest['method'];
        } else {
            $request['method'] = null;
        }

        if (array_key_exists('params', $jsonRequest)) {
            $request['params'] = $jsonRequest['params'];
        } else {
            $request['params'] = [];
        }

        if (array_key_exists('id', $jsonRequest)) {
            $request['id'] = $jsonRequest['id'];
        } else {
            $request['id'] = '0';
        }

        return $request;
    }

    /**
     * Process httpRequest for get method name
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return string
     */
    public function getHttpRequestMethod(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
        $request = $this->parserRequest($httpRequest);

        return array_key_exists('method', $request) ? $request['method'] : '';
    }

    /**
     * Process httpRequest for get method params
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return array
     */
    public function getHttpRequestParams(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
        $request = $this->parserRequest($httpRequest);

        return array_key_exists('params', $request) ? $request['params'] : [];
    }


    /**
     * Process httpRequest for get method extra
     *
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @return array
     */
    public function getHttpRequestExtra(\Symfony\Component\HttpFoundation\Request &$httpRequest)
    {
        $request = $this->parserRequest($httpRequest);
        $extra   = ['id' => '', 'jsonrpc' => ''];

        if (array_key_exists('id', $request)) {
            $extra['id'] = $request['id'];
        } else {
            $extra['id'] = 0;
        }

        if (array_key_exists('jsonrpc', $request)) {
            $extra['jsonrpc'] = $request['jsonrpc'];
        } else {
            $extra['jsonrpc'] = '2.0';
        }

        if (empty($extra['jsonrpc'])) {
            $extra['jsonrpc'] = '2.0';
        }

        return $extra;
    }

    /**
     * Process result
     *
     * @param \Symfony\Component\HttpFoundation\Request  $httpRequest
     * @param \Symfony\Component\HttpFoundation\Response $httpResponse
     * @param Result                                     $result
     */
    public function processResult(\Symfony\Component\HttpFoundation\Request &$httpRequest, \Symfony\Component\HttpFoundation\Response &$httpResponse, Result &$result)
    {
        $extra                   = $this->getHttpRequestExtra($httpRequest);
        $jsonResponse            = [];
        $jsonResponse['jsonrpc'] = $extra['jsonrpc'];
        $jsonResponse['id']      = $extra['id'];

        if (empty($result->getError())) {
            $jsonResponse['error']  = null;
            $jsonResponse['result'] = (array)$result->getResult();
        } else {
            $jsonResponse['error']  = (array)$result->getError();
            $jsonResponse['result'] = null;
        }

        $httpResponse->headers->set('Content-Type', 'application/json');
        $httpResponse->setContent(json_encode($jsonResponse));
    }
}
