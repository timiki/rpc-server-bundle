<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
	 * @return Proxy
	 */
	public function getProxy()
	{
		return $this->container->get('rpc.proxy');
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

		$json = json_decode($httpRequest->getContent(), true);

		if ($json === null) {
			return null;
		}

		$parseJsonRequest = function ($json) {

			$jsonrpc = array_key_exists('jsonrpc', $json) ? $json['jsonrpc'] : '2.0';
			$id      = array_key_exists('id', $json) ? $json['id'] : null;
			$method  = array_key_exists('method', $json) ? $json['method'] : null;
			$params  = array_key_exists('params', $json) ? $json['params'] : [];

			return new JsonRequest($jsonrpc, $id, $method, (array)$params);

		};

		if (array_keys($json) === range(0, count($json) - 1)) {
			foreach ($json as $part) {
				$requests[] = $parseJsonRequest($part);
			}
		} else {
			$requests[] = $parseJsonRequest($json);
		}

		return $requests;
	}

	/**
	 * Handle json request
	 *
	 * @param JsonRequest $jsonRequest
	 * @return JsonResponse
	 */
	public function handleJsonRequest(JsonRequest $jsonRequest)
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

			$proxy = $this->getProxy();

			if ($proxy->isEnable()) {

				if ($response = $proxy->handleJsonRequest($jsonRequest)) {
					return $response;
				} else {
					$jsonResponse = new JsonResponse();
					$jsonResponse->setId($jsonRequest->getId());
					$jsonResponse->setErrorCode('-32601');
					$jsonResponse->setErrorMessage('Method not found');

					return $jsonResponse;
				}

			} else {

				$jsonResponse = new JsonResponse();
				$jsonResponse->setId($jsonRequest->getId());
				$jsonResponse->setErrorCode('-32601');
				$jsonResponse->setErrorMessage('Method not found');

				return $jsonResponse;
			}
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

		// Build and validate methods params

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
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse
	 */
	public function handleHttpRequest(HttpRequest $httpRequest)
	{
		/* @var JsonRequest[] $jsonRequests */

		if (!$jsonRequests = $this->parserHttpRequest($httpRequest)) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setErrorCode('-32700');
			$jsonResponse->setErrorMessage('Parse error');

			return $jsonResponse->getHttpResponse();
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

		// Single request

		if (count($jsonRequests) === 1 && count($jsonResponses) === 1 && count($results) === 1) {

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
						$cookie = new \Symfony\Component\HttpFoundation\Cookie($cookeArray['name'], $cookeArray['value'], $cookeArray['expire'], $cookeArray['path'], $cookeArray['domain'], $cookeArray['secure'], $cookeArray['httpOnly']);
						$httpResponse->headers->setCookie($cookie);
					}

				}
			}
		}

		$httpResponse->headers->set('Content-Type', 'application/json');
		$httpResponse->setContent(json_encode($results));

		return $httpResponse;
	}
}
