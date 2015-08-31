<?php

namespace Timiki\Bundle\RpcServerBundle\Method;

/**
 * Result - simple class for hold RPC Method result value
 */

class Result
{
    /**
     * $_result
     *
     * @var mixed
     */
    protected $_result = null;

    /**
     * $_error
     *
     * @var mixed
     */
    protected $_error = [];

    /**
     * Set result value
     *
     * @param mixed $result
     * @return Result
     */
    public function setResult($result)
    {
        $this->_result = $result;

        return $this;
    }

    /**
     * Set result error
     *
     * @param mixed $error
     * @return Result
     */
    public function setError($error)
    {
        $this->_error[] = $error;

        return $this;
    }

    /**
     * Is set result
     *
     * @return boolean
     */
    public function isResult()
    {
        return ($this->_result === null) ? false : true;
    }

    /**
     * Is set error
     *
     * @return boolean
     */
    public function isError()
    {
        return (count($this->_error) > 0) ? true : false;
    }

    /**
     * Get error value
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Get result value
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }
}