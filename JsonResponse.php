<?php

namespace Timiki\Bundle\RpcServerBundle;

use Exception;
use Timiki\RpcCommon\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    /**
     * @var null|Exception
     */
    protected $exception;

    /**
     * Get error exception.
     *
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set error exception.
     *
     * @param null|Exception $exception
     */
    public function setException(Exception $exception = null)
    {
        $this->exception = $exception;
    }
}
