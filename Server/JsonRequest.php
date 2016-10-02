<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\RpcClient\JsonRequest as BaseJsonRequest;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class JsonRequest.
 */
class JsonRequest extends BaseJsonRequest
{

    /**
     * @var null|HttpRequest
     */
    protected $httpRequest;

    /**
     * @param string $method
     * @param array $params
     * @param string $id
     */
    public function __construct($method, $params = [], $id = null)
    {
        parent::__construct($method, (array)$params, $id);

        // Fix input params as mixed
        $this->params = $params;
    }

    /**
     * Set http request.
     *
     * @param HttpRequest|null $request
     * @return $this
     */
    public function setHttpRequest(HttpRequest $request = null)
    {
        $this->httpRequest = $request;

        return $this;
    }

    /**
     * Get http request.
     *
     * @return null|HttpRequest
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }
}
