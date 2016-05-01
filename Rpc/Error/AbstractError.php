<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

use Timiki\Bundle\RpcServerBundle\Rpc\JsonRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class AbstractError
{
	/**
	 * JsonRequest
	 *
	 * @var JsonRequest
	 */
	protected $jsonRequest;

	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $code = '-32000';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Error data
	 *
	 * @var mixed|null
	 */
	protected $data;

	/**
	 * Create new method
	 *
	 * @param null|JsonRequest $jsonRequest
	 */
	public function __construct(JsonRequest $jsonRequest = null)
	{
		$this->jsonRequest = $jsonRequest;
	}

	/**
	 * Return array response
	 *
	 * @return array
	 */
	public function getArrayResponse()
	{
		$json                     = [];
		$json['jsonrpc']          = '2.0';
		$json['error']            = [];
		$json['error']['code']    = $this->code;
		$json['error']['message'] = $this->message;

		if (!empty($this->data)) {
			$json['error']['data'] = $this->data;
		}

		$json['id'] = is_object($this->jsonRequest) ? $this->jsonRequest->getId() : null;

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
	 * Set error data
	 *
	 * @param mixed $data
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}
}
