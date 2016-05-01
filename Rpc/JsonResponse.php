<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Timiki\RpcClient\Client\HttpResponse as ProxyHttpResponse;

class JsonResponse
{
	/**
	 * JsonRpc
	 *
	 * @var string
	 */
	protected $jsonrpc = '2.0';

	/**
	 * Id
	 *
	 * @var integer|null
	 */
	protected $id = null;

	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $errorCode;

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * Error data
	 *
	 * @var mixed|null
	 */
	protected $errorData;

	/**
	 * Result
	 *
	 * @var mixed|null
	 */
	protected $result;

	/**
	 * Proxy http response
	 *
	 * @var ProxyHttpResponse|null
	 */
	protected $proxy;

	/**
	 * Create new JsonResponse
	 *
	 * @param JsonRequest $jsonRequest
	 */
	public function __construct(JsonRequest $jsonRequest = null)
	{
		if ($jsonRequest) {
			$this->id      = $jsonRequest->getId();
			$this->jsonrpc = $jsonRequest->getJsonrpc();

			if (!$jsonRequest->isValid()) {
				$this->errorCode    = '-32600';
				$this->errorMessage = 'Invalid Request';
			}
		}
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id
	 *
	 * @param int|null $id
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get error code
	 *
	 * @return string
	 */
	public function getErrorCode()
	{
		return $this->errorCode;
	}

	/**
	 * Set error code
	 *
	 * @param string $errorCode
	 * @return $this
	 */
	public function setErrorCode($errorCode)
	{
		$this->errorCode = $errorCode;

		return $this;
	}

	/**
	 * Get error message
	 *
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	/**
	 * Set error message
	 *
	 * @param string $errorMessage
	 * @return $this
	 */
	public function setErrorMessage($errorMessage)
	{
		$this->errorMessage = $errorMessage;

		return $this;
	}

	/**
	 * Get error data
	 *
	 * @return mixed|null
	 */
	public function getErrorData()
	{
		return $this->errorData;
	}

	/**
	 * Set error data
	 *
	 * @param mixed|null $errorData
	 * @return $this
	 */
	public function setErrorData($errorData)
	{
		$this->errorData = $errorData;

		return $this;
	}

	/**
	 * Get result
	 *
	 * @return mixed|null
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Set result
	 *
	 * @param mixed|null $result
	 * @return $this
	 */
	public function setResult($result)
	{
		$this->result = $result;

		return $this;
	}

	/**
	 * Return array response
	 *
	 * @return array
	 */
	public function getArrayResponse()
	{
		$json            = [];
		$json['jsonrpc'] = '2.0';

		if ($this->errorCode) {

			$json['error']            = [];
			$json['error']['code']    = $this->errorCode;
			$json['error']['message'] = $this->errorMessage;

			if (!empty($this->errorData)) {
				$json['error']['data'] = $this->errorData;
			}

		} else {

			if (!empty(!$this->result)) {
				$json['result'] = $this->result;
			}

		}

		$json['id'] = !empty($this->id) ? $this->id : null;

		return $json;
	}

	/**
	 * Return HttpResponse
	 *
	 * @return HttpResponse
	 */
	public function getHttpResponse()
	{
		$httpResponse = HttpResponse::create();

		$httpResponse->headers->set('Content-Type', 'application/json');
		$httpResponse->setContent(json_encode($this->getArrayResponse()));

		return $httpResponse;
	}

	/**
	 * Is response error
	 *
	 * @return boolean
	 */
	public function isError()
	{
		return !empty($this->errorCode);
	}

	/**
	 * Is response from proxy
	 *
	 * @return boolean
	 */
	public function isProxy()
	{
		return !empty($this->errorCode);
	}

	/**
	 * Get proxy http response
	 *
	 * @return null|ProxyHttpResponse
	 */
	public function getProxy()
	{
		return $this->proxy;
	}

	/**
	 * Set proxy http response
	 *
	 * @param null|ProxyHttpResponse $proxy
	 * @return $this
	 */
	public function setProxy($proxy)
	{
		$this->proxy = $proxy;

		return $this;
	}
}
