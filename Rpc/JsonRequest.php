<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

class JsonRequest
{
	/**
	 * JsonRpc
	 *
	 * @var string
	 */
	protected $jsonrpc;

	/**
	 * Id
	 *
	 * @var integer|null
	 */
	protected $id = null;

	/**
	 * Method
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Params
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Create new JsonRequest
	 *
	 * @param string  $jsonrpc
	 * @param integer $id
	 * @param string  $method
	 * @param array   $params
	 */
	public function __construct($jsonrpc, $id, $method, array $params)
	{
		$this->jsonrpc = $jsonrpc;
		$this->id      = $id;
		$this->method  = $method;
		$this->params  = $params;
	}

	/**
	 * Get jsonrpc
	 *
	 * @return string|null
	 */
	public function getJsonrpc()
	{
		return $this->jsonrpc;
	}

	/**
	 * Get id
	 *
	 * @return integer|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get method
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Get params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Is valid
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return !empty($this->jsonrpc) && !empty($this->method);
	}
}
