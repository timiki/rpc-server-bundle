<?php

namespace Timiki\Bundle\RpcServerBundle\Server;

use Timiki\RpcClient\JsonRequest as BaseJsonRequest;

/**
 * Class JsonRequest.
 */
class JsonRequest extends BaseJsonRequest
{

    /**
     * @param string $method
     * @param array  $params
     * @param string $id
     */
    public function __construct($method, $params = [], $id = null)
    {
        parent::__construct($method, (array)$params, $id);

        // Fix input params as mixed
        $this->params = $params;
    }

}
