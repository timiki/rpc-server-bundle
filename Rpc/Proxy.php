<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Timiki\RpcClient\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RPC Proxy
 */
class Proxy
{
	/**
	 * RPC client
	 *
	 * @var Client
	 */
	protected $client;

	/**
	 * Options
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Container
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Create new proxy
	 *
	 * @param array              $options
	 * @param ContainerInterface $container
	 */
	public function __construct(array $options = [], ContainerInterface $container = null)
	{
		$this->setContainer($container);
		$this->options = $options;

		$headers = (array)$options['headers'];
		$cookies = (array)$options['cookies'];

		if (is_array($options['forwardHeaders']) and !empty($options['forwardHeaders'])) {
			foreach ($options['forwardHeaders'] as $header) {
				$headers[$header] = Request::createFromGlobals()->headers->get($header);
				if (strtolower($header) == 'client-ip') {
					$headers[$header] = [Request::createFromGlobals()->getClientIp()];
				}
			}
		}

		if (is_array($options['forwardCookies']) and !empty($options['forwardCookies'])) {
			foreach (Request::createFromGlobals()->cookies->all() as $name => $values) {
				if (in_array($name, $options['forwardCookies'])) {
					$cookies[$name] = $values;
				}
			}
		}

		$this->setClient(new Client($options['address'], $headers, $cookies));
	}

	/**
	 * Set container
	 *
	 * @param ContainerInterface|null $container
	 * @return $this
	 */
	public function setContainer(ContainerInterface $container)
	{
		if ($container instanceof ContainerInterface) {
			$this->container = $container;
		}

		return $this;
	}

	/**
	 * Get container
	 *
	 * @return ContainerInterface|null
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Get option
	 *
	 * @param string $name    Option name
	 * @param mixed  $default Option default value
	 * @return mixed
	 */
	public function getOption($name, $default = null)
	{
		if (array_key_exists($name, $this->options)) {
			return $this->options[$name];
		}

		return $default;
	}

	/**
	 * Set proxy client
	 *
	 * @param Client $client
	 * @return $this
	 */
	public function setClient(Client $client)
	{
		$this->client = $client;

		return $this;
	}

	/**
	 * Get proxy client
	 *
	 * @return Client
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Handle json request
	 *
	 * @param JsonRequest $jsonRequest
	 * @return JsonResponse|null
	 */
	public function handleJsonRequest(JsonRequest $jsonRequest)
	{
		if ($this->getContainer()) {
			$this->getContainer()->get('session')->save();
		}

		$jsonResponse = null;

		if ($response = $this->client->call($jsonRequest->getMethod(), $jsonRequest->getParams(), $jsonRequest->getId())) {

			$jsonResponse = new JsonResponse();
			$jsonResponse->setId($response->getId());
			$jsonResponse->setErrorCode($response->getErrorCode());
			$jsonResponse->setErrorMessage($response->getErrorMessage());
			$jsonResponse->setErrorData($response->getErrordata());
			$jsonResponse->setResult($response->getResult());
			$jsonResponse->setProxy($response->getHttpResponse());

			return $jsonResponse;
		}

		if ($this->getContainer() !== null) {
			$this->getContainer()->get('session')->migrate();
		}

		return $jsonResponse;
	}

	/**
	 * Is proxy is enable
	 *
	 * @return boolean
	 */
	public function isEnable()
	{
		return array_key_exists('enable', $this->options) ? (boolean)$this->options['enable'] : false;
	}
}
