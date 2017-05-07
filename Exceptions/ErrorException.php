<?php

namespace Timiki\Bundle\RpcServerBundle\Exceptions;

use RuntimeException;

/**
 * RPC error exception.
 */
class ErrorException extends RuntimeException
{
    /**
     * Exception data.
     *
     * @var mixed
     */
    private $data;

    /**
     * Exception id.
     *
     * @var mixed
     */
    private $id;

    /**
     * ErrorException constructor.
     *
     * @param string $message
     * @param int $code
     * @param null $data
     * @param null $id
     */
    public function __construct($message = '', $code = 0, $data = null, $id = null)
    {
        $this->data = $data;

        parent::__construct($message, $code);
    }

    /**
     * Get data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

