<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * RPC handler
 */
class Handler
{
	/**
	 * Server methods
	 *
	 * @var array
	 */
	protected $methods = [];

	/**
	 * Server namespace methods
	 *
	 * @var array
	 */
	protected $namespace = [];

	/**
	 * Container
	 *
	 * @var ContainerInterface|null
	 */
	protected $container;

	/**
	 * Create new instance of JSON-RPC server
	 *
	 * @param array              $methods   Methods array [name => class]
	 * @param array              $namespace Namespace array
	 * @param ContainerInterface $container Instance of container
	 */
	public function __construct(array $methods = [], array $namespace = [], ContainerInterface $container = null)
	{
		$this->container = $container;
		$this->methods   = $methods;
		$this->namespace = $namespace;
	}

	/**
	 * Get container instance
	 *
	 * @return ContainerInterface|null
	 */
	public function &getContainer()
	{
		return $this->container;
	}

	/**
	 * Get method
	 *
	 * @param string $method Method name
	 * @return null|Method
	 */
	public function getMethod($method)
	{

		if (array_key_exists($method, $this->methods)) {

			$class = $this->methods[$method];

			if (class_exists($class)) {
				/* @var Method $methodObject */
				$methodObject = new $class();
				$methodObject->setHandler($this);

				return $methodObject;
			}

		}

		foreach ($this->namespace as $namespace) {

			$class = $namespace.'\\'.$method;
			try {
				if (class_exists($class)) {
					/* @var Method $methodObject */
					$methodObject = new $class();
					$methodObject->setHandler($this);

					return $methodObject;
				}
			} catch (\Exception $e) {
			}

		}

		return null;
	}

	/**
	 * Get Rpc proxy
	 *
	 * @return Proxy|null
	 */
	public function getProxy()
	{
		if ($this->container) {
			return $this->container->get('rpc.proxy');
		}

		return null;
	}

	/**
	 * Parser HttpRequestRequest to JsonRequest array
	 *
	 * @param HttpRequest $httpRequest
	 * @return JsonRequest[]|array|null
	 */
	protected function parserHttpRequest(HttpRequest $httpRequest)
	{
		$requests = [];

		$json = json_decode($httpRequest->getContent(), true);

		if ($json === null || !is_array($json)) {
			return null;
		}

		$parseJsonRequest = function ($json) {

			$jsonrpc = null;
			$id      = null;
			$method  = null;
			$params  = null;

			if (is_array($json)) {
				$jsonrpc = array_key_exists('jsonrpc', $json) ? $json['jsonrpc'] : '2.0';
				$id      = array_key_exists('id', $json) ? $json['id'] : null;
				$method  = array_key_exists('method', $json) ? $json['method'] : null;
				$params  = array_key_exists('params', $json) ? $json['params'] : [];
			}

			return new JsonRequest($jsonrpc, $id, $method, $params);

		};

		if (array_keys($json) === range(0, count($json) - 1)) {
			foreach ($json as $part) {
				$requests[] = $parseJsonRequest($part);
			}
		} else {
			$requests = $parseJsonRequest($json);
		}

		return $requests;
	}

	/**
	 * Get response error
	 *
	 * @param int        $id
	 * @param int        $errorCode
	 * @param string     $errorMessage
	 * @param mixed|null $errorData
	 * @return JsonResponse
	 */
	public function responseError($id, $errorCode = -32603, $errorMessage = 'Internal error', $errorData = null)
	{
		$jsonResponse = new JsonResponse();
		$jsonResponse->setId($id);
		$jsonResponse->setErrorCode((integer)$errorCode);
		$jsonResponse->setErrorMessage($errorMessage);
		$jsonResponse->setErrorData($errorData);

		return $jsonResponse;
	}

	/**
	 * Handle json request
	 *
	 * @param JsonRequest $jsonRequest
	 * @return JsonResponse
	 */
	public function handleJsonRequest(JsonRequest $jsonRequest)
	{
		// TODO: logging
		// TODO: events

		// Is valid request

		if (!$jsonRequest->isValid()) {
			return $this->responseError($jsonRequest->getId(), -32600, 'Invalid Request');
		}

		// Get method

		if (!$method = $this->getMethod($jsonRequest->getMethod())) {

			if ($proxy = $this->getProxy()) {

				if ($proxy->isEnable()) {

					if ($response = $proxy->handleJsonRequest($jsonRequest)) {
						return $response;
					} else {
						return $this->responseError($jsonRequest->getId(), -32601, 'Method not found');
					}

				} else {
					return $this->responseError($jsonRequest->getId(), -32601, 'Method not found');

				}
			} else {
				return $this->responseError($jsonRequest->getId(), -32601, 'Method not found');
			}

		}


		$reflection = new  \ReflectionObject($method);


		// Check roles

		if ($reflection->hasMethod('getRoles')) {

			$isGranted = [];

			if ($this->container) {
				foreach ($reflection->getMethod('getRoles')->invoke($method) as $role) {
					$isGranted[] = $this->getContainer()->get('security.authorization_checker')->isGranted($role);
				}
			}

			if (in_array(false, $isGranted, true)) {
				return $this->responseError($jsonRequest->getId(), -32001, 'Method not granted');
			}

		}

		// Set values

		$method->setValues($jsonRequest->getParams());

		// Check if set constraints

		if ($reflection->hasMethod('getConstraints')) {

			$constraints = $reflection->getMethod('getConstraints')->invoke($method);

			if ($constraints instanceof Collection) {

				/* @var ConstraintViolationListInterface $error */
				$error = $this->getContainer('validator')->validate($method->getParams(), $constraints);

				if ($error->count() > 0) {
					return $this->responseError($jsonRequest->getId(), -32602, 'Invalid params', (array)$error);
				}

			} else {

				return $this->responseError($jsonRequest->getId(), -32603, 'Internal error');

			}

		}

		// Check if has validate

		if ($reflection->hasMethod('validate')) {

			if (!(bool)$reflection->getMethod('validate')->invoke($method)) {
				return $this->responseError($jsonRequest->getId(), -32602, 'Invalid params');
			}

		}

		// Execute

		if ($reflection->hasMethod('execute')) {

			// inject params

			$args = [];
			foreach ($reflection->getMethod('execute')->getParameters() as $param) {

				if (array_key_exists($param->getName(), $method->getValues())) {
					$args[] = $method->getValues()[$param->getName()];
				} else {
					$args[] = null;
				}

			}

			// call method

			try {
				$result = $reflection->getMethod('execute')->invokeArgs($method, $args);;
			} catch (\Exception $e) {

				return $this->responseError($jsonRequest->getId(), -32603, 'Internal error');

			}

		} else {

			return $this->responseError($jsonRequest->getId(), -32603, 'Internal error');

		}


		if (empty($method->getResult()) && !empty($result)) {
			$method->result($result);
		}

		$jsonResponse = new JsonResponse();
		$jsonResponse->setId($jsonRequest->getId());

		if ($method->isError()) {
			$jsonResponse->setErrorCode('-32000');
			$jsonResponse->setErrorMessage('Method error');
			$jsonResponse->setErrorData($method->getErrors());
		} else {
			$jsonResponse->setResult($method->getResult());
		}

		return $jsonResponse;
	}

	/**
	 * Handle http request
	 *
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse
	 */
	public function handleHttpRequest(HttpRequest $httpRequest)
	{
		/* @var JsonRequest[] $jsonRequests */

		if (!$jsonRequests = $this->parserHttpRequest($httpRequest)) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setErrorCode(-32700);
			$jsonResponse->setErrorMessage('Parse error');

			return $jsonResponse->getHttpResponse();
		}

		$count = 0;

		if ($jsonRequests instanceof JsonRequest) {
			$jsonRequests = [$jsonRequests];
		} else {
			$count = count($jsonRequests);
		}

		/* @var JsonResponse[] $jsonResponses */

		$jsonResponses = [];
		foreach ($jsonRequests as $request) {
			$jsonResponses[] = $this->handleJsonRequest($request);
		}

		$results      = [];
		$httpResponse = HttpResponse::create();


		foreach ($jsonResponses as $jsonResponse) {

			if ($jsonResponse->isError()) {
				$results[] = $jsonResponse->getArrayResponse();
			} elseif ($jsonResponse->getId()) {
				$results[] = $jsonResponse->getArrayResponse();
			}

		}

		// Single request with response

		if ($count === 0 && count($results) === 1) {

			$results = $results[0];

			$jsonResponse = $jsonResponses[0];

			// Proxy cookies for simple request

			if ($jsonResponse->isProxy()) {

				// is set proxy cookies

				$cookiesForward  = $this->getProxy()->getOption('forwardCookies', []);
				$responseCookies = $jsonResponse->getProxy()->getHeader('set-cookie', []);

				foreach ($responseCookies as $cookeRaw) {
					// Parse cookie string
					$cookeRawArray = explode(';', $cookeRaw);
					$cookeArray    = ['name' => '', 'value' => '', 'expire' => 0, 'path' => '/', 'domain' => null, 'secure' => false, 'httpOnly' => true];

					foreach ($cookeRawArray as $key => $cookeRawArrayPart) {
						$part = explode('=', $cookeRawArrayPart);
						if ($key === 0) {
							$cookeArray['name']  = $part[0];
							$cookeArray['value'] = $part[1];
						} else {
							switch (trim($part[0])) {
								case 'expire':
									$cookeArray['expire'] = intval($part[1]);
									break;
								case 'path':
									$cookeArray['path'] = $part[1];
									break;
								case 'domain':
									if (!empty($this->getProxy()->getOptions()['forwardCookiesDomain'])) {
										$cookeArray['domain'] = $this->getProxy()->getOptions()['forwardCookiesDomain'];
									} else {
										$cookeArray['domain'] = $part[1];
									}
									break;
								case 'secure':
									$cookeArray['secure'] = boolval($part[1]);
									break;
								case 'httpOnly':
									$cookeArray['httpOnly'] = boolval($part[1]);
									break;
							}
						}
					}

					if (in_array($cookeArray['name'], $cookiesForward)) {
						$cookie = new Cookie($cookeArray['name'], $cookeArray['value'], $cookeArray['expire'], $cookeArray['path'], $cookeArray['domain'], $cookeArray['secure'], $cookeArray['httpOnly']);
						$httpResponse->headers->setCookie($cookie);
					}

				}
			}
		}

		$httpResponse->headers->set('Content-Type', 'application/json');

		if (!empty($results)) {
			$httpResponse->setContent(json_encode($results));
		}

		return $httpResponse;
	}
}
