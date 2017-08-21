<?php

namespace Timiki\Bundle\RpcServerBundle;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Timiki\RpcCommon\JsonRequest as BaseJsonRequest;

class JsonRequest extends BaseJsonRequest
{
    /**
     * @var null|HttpRequest
     */
    protected $httpRequest;

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
