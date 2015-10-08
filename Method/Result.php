<?php

namespace Timiki\Bundle\RpcServerBundle\Method;

/**
 * Result - simple class for hold RPC Method result value
 */

class Result
{
    /**
     * result
     */
    protected $result = null;

    /**
     * error
     */
    protected $error = [];

    /**
     * proxy
     */
    protected $proxy = null;

    /**
     * Set result value
     *
     * @param mixed $result
     * @return Result
     */
    public function setResult($result)
    {
        $this->result = $result;

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
        $this->error[] = $error;

        return $this;
    }

    /**
     * Is set result
     *
     * @return boolean
     */
    public function isResult()
    {
        return ($this->result === null) ? false : true;
    }

    /**
     * Is set error
     *
     * @return boolean
     */
    public function isError()
    {
        return (count($this->error) > 0) ? true : false;
    }

    /**
     * Get error value
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get result value
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get proxy result (rpc client result)
     *
     * @return \Timiki\RpcClientCommon\Client\Response
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Set proxy result (rpc client result)
     *
     * @return Result
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }
}