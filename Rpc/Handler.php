<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Timiki\Bundle\RpcServerBundle\Server\Handler as HandlerInterface;
use Timiki\Bundle\RpcServerBundle\Method\Result;

/**
 * RPC handler
 */
class handler
{
	/**
	 * Server methods
	 *
	 * @var array
	 */
	protected $methods = [];

	/**
	 * Server methods paths
	 *
	 * @var array
	 */
	protected $paths = [];

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
	 * @param array              $paths     Paths array [namespace => path]
	 * @param ContainerInterface $container Instance of container
	 */
	public function __construct(array $methods = [], array $paths = [], ContainerInterface $container = null)
	{
		$this->container = $container;

		foreach ($methods as $name => $class) {
			$this->methods[$name] = $class;
		}

		foreach ($paths as $namespace => $path) {
			$this->paths[$namespace] = $path;
		}
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

		foreach ($this->paths as $path => $namespace) {

			$class = $namespace.'\\'.$method;
			if (class_exists($class)) {
				/* @var Method $methodObject */
				$methodObject = new $class();
				$methodObject->setHandler($this);

				return $methodObject;
			}

		}

		return null;
	}

	/**
	 * Get Rpc proxy
	 *
	 * @return Proxy
	 */
	public function getProxy()
	{
		return $this->container->get('rpc.proxy');
	}

	/**
	 * Call method
	 *
	 * @param string|integer $id
	 * @param string         $method Method name
	 * @param array          $params Method params
	 * @return mixed Return method result value
	 */
	public function call($id, $method, array $params = [])
	{
		$methodObject = $this->getMethod($method);
		$result       = new Result();
		if ($methodObject !== null) {

			// Prepare methods params value
			$methodParams = [];

			foreach ($methodObject->getParams() as $value) {
				if (array_key_exists($value[0], $params)) {
					$methodParams[$value[0]] = $params[$value[0]];
				} else {
					// Set default or null
					if (array_key_exists(2, $value)) {
						$methodParams[$value[0]] = $value[2];
					}
					// else {
					// $methodParams[$value[0]] = null;
					// }
				}
			}

			// Validate methods params
			$validator      = new Validator();
			$validateResult = $validator->validate($methodObject, $methodParams);

			if (count($validateResult) > 0) {
				// have some errors
				$result->setError($validateResult);
			} else {

				$reflection = new  \ReflectionObject($methodObject);

				/*
				| Reflection method beforeExecute function
				*/
				if ($reflection->hasMethod('beforeExecute')) {
					$methodBeforeExecuteParams = $reflection->getMethod('beforeExecute')->getParameters();
					$args                      = [];

					foreach ($methodBeforeExecuteParams as $param) {
						if ($param->getName() == 'result') {
							$args[] = &$result;
						} elseif ($param->getName() == 'extra') {
							$args[] = $extra;
						} else {
							if (array_key_exists($param->getName(), $methodParams)) {
								$args[] = $methodParams[$param->getName()];
							} else {
								$args[] = null;
							}
						}
					}

					$reflection->getMethod('beforeExecute')->invokeArgs($methodObject, $args);
				}

				if (!$result->isError()) {
					/*
					| Reflection method execute function
					*/
					if ($reflection->hasMethod('execute')) {
						$methodExecuteParams = $reflection->getMethod('execute')->getParameters();
						$args                = [];
						foreach ($methodExecuteParams as $param) {
							if ($param->getName() == 'result') {
								$args[] = &$result;
							} elseif ($param->getName() == 'extra') {
								$args[] = $extra;
							} else {
								if (array_key_exists($param->getName(), $methodParams)) {
									$args[] = $methodParams[$param->getName()];
								} else {
									$args[] = null;
								}
							}
						}
						$reflection->getMethod('execute')->invokeArgs($methodObject, $args);
					}
				}
			}
		} else {
			// Use Proxy?
			if ($this->isProxy()) {
				$proxy       = $this->getProxy();
				$proxyResult = $proxy->callMethod($method, $params, $extra);

				// Proxy errors
				if (!empty($proxyResult->getResult()->error)) {
					foreach ($proxyResult->getResult()->error as $e) {
						$result->setError($e);
					}
				}

				// Proxy result
				$result->setProxy($proxyResult);
				$result->setResult($proxyResult->getResult()->result);

			} else {
				$result->setError(['error' => 'methodNotFound', 'message' => 'Method not found']);
			}
		}

		return $result;
	}

	/**
	 * Parser HttpRequestRequest to JsonRequest array
	 *
	 * @param HttpRequest $httpRequest
	 * @return JsonRequest[]|null
	 */
	protected function parserHttpRequest(HttpRequest $httpRequest)
	{
		$requests = [];

		try {
			$json = json_decode($httpRequest->getContent(), true);
		} catch (\Exception $e) {
			return null;
		}

		$parseJsonRequest = function ($json) use ($requests) {

			$jsonrpc = array_key_exists('jsonrpc', $json) ? $json['jsonrpc'] : null;
			$id      = array_key_exists('id', $json) ? $json['id'] : null;
			$method  = array_key_exists('method', $json) ? $json['method'] : null;
			$params  = array_key_exists('params', $json) ? $json['params'] : null;

			$requests[] = new JsonRequest($jsonrpc, $id, $method, $params);

		};

		if (array_keys($json) !== range(0, count($json) - 1)) {
			$parseJsonRequest($json);
		} else {
			foreach ($json as $part) {
				$parseJsonRequest($part);
			}
		}

		return $requests;
	}

	/**
	 * Handle json request
	 *
	 * @param jsonRequest $jsonRequest
	 * @return JsonResponse
	 */
	public function handleJsonRequest(jsonRequest $jsonRequest)
	{

		// Is valid request

		if (!$jsonRequest->isValid()) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($jsonRequest->getId());
			$jsonResponse->setErrorCode('-32600');
			$jsonResponse->setErrorMessage('Invalid Request');

			return $jsonResponse;
		}

		// Get method

		if (!$method = $this->getMethod($jsonRequest->getMethod())) {

			$this->getProxy();


			// TODO: proxy

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($jsonRequest->getId());
			$jsonResponse->setErrorCode('-32601');
			$jsonResponse->setErrorMessage('Method not found');

			return $jsonResponse;
		}

		// Is granted

		$isGranted = [];
		foreach ($method->getRoles() as $role) {
			$isGranted[] = $this->getContainer()->get('security.authorization_checker')->isGranted($role);
		}

		if (in_array(false, $isGranted, true)) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($jsonRequest->getId());
			$jsonResponse->setErrorCode('-32001');
			$jsonResponse->setErrorMessage('Method not granted');

			return $jsonResponse;
		}

		// Validate methods params

		$validator = new Validator();

		if ($validatorResult = $validator->validate($method, $jsonRequest->getParams())) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($jsonRequest->getId());
			$jsonResponse->setErrorCode('-32602');
			$jsonResponse->setErrorMessage('Invalid params');
			$jsonResponse->setErrorData($validatorResult);

			return $jsonResponse;
		}

		// Set values

		$method->setValues($jsonRequest->getParams());

		// Execute

		try {
			$method->execute();
		} catch (\Exception $e) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($jsonRequest->getId());
			$jsonResponse->setErrorCode('-32603');
			$jsonResponse->setErrorMessage('Internal error');

			return $jsonResponse;
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
	 * @param HttpRequest  $httpRequest
	 * @param HttpResponse $httpResponse
	 * @return HttpResponse
	 */
	public function handleHttpRequest(HttpRequest $httpRequest, HttpResponse $httpResponse = null)
	{
		/*
		| Parse HttpRequest
		*/
		if (!$requests = $this->parserHttpRequest($httpRequest)) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setErrorCode('-32700');
			$jsonResponse->setErrorMessage('Parse error');

			return $jsonResponse->getHttpResponse();
		}


		return;


		/*
		| Get HttpRequest handler
		*/

		if (class_exists('\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\'.ucfirst(strtolower($type)))) {
			$handlerClass = '\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\'.ucfirst(strtolower($type));
			$handler      = new $handlerClass();
		} else {
			$handlerClass = '\\Timiki\\Bundle\\RpcServerBundle\\Server\\Handlers\\'.ucfirst(strtolower($this->defaultHandlers));
			$handler      = new $handlerClass();
		}

		/* @var HandlerInterface $handler */
		$handler->setServer($this);

		/*
		| Process HttpRequest
		*/

		$methodName   = $handler->getHttpRequestMethod($httpRequest);
		$methodParams = $handler->getHttpRequestParams($httpRequest);
		$methodExtra  = $handler->getHttpRequestExtra($httpRequest);

		/*
		| Execute method
		*/

		$result = $this->call($methodName, $methodParams, $methodExtra);

		/*
		| Is proxy?
		*/

		if ($this->isProxy() && $result->getProxy() !== null) {
			// is set proxy cookies
			$cookiesForward  = $this->getProxy()->getOption('forwardCookies', []);
			$responseCookies = $result->getProxy()->getHttpResponse()->getHeader('set-cookie', []);
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
					$cookie = new \Symfony\Component\HttpFoundation\Cookie($cookeArray['name'], $cookeArray['value'], $cookeArray['expire'], $cookeArray['path'], $cookeArray['domain'], $cookeArray['secure'], $cookeArray['httpOnly']);
					$httpResponse->headers->setCookie($cookie);
				}
			}
		}

		/*
		| Process HttpResponse
		*/

		$handler->processResult($httpRequest, $httpResponse, $result);

		return $httpResponse;
	}
}
