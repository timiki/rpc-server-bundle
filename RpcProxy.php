<?php

namespace Timiki\Bundle\RpcServerBundle;

use Timiki\RpcClientCommon\Client;
use Timiki\RpcClientCommon\Client\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\HttpFoundation\Request;

/**
 * RPC Proxy instance
 */
class RpcProxy
{
	/**
	 * Proxy locale (default en)
	 *
	 * @var array
	 */
	protected $locale = 'en';

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
	 * @param array $options
	 * @param string $locale
	 * @param ContainerInterface $container
	 */
	public function __construct(array $options = [], $locale = 'en', ContainerInterface $container = null)
	{
		$this->setLocale($locale);
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

		$this->setClient(new Client($options['address'], $options['type'], $headers, $cookies, $this->getLocale()));
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
	 * @param string $name Option name
	 * @param mixed $default Option default value
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
	 * Set server locale
	 *
	 * @param string $locale
	 * @return $this
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;

		return $this;
	}

	/**
	 * Get server locale
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * Call method
	 *
	 * @param string $method
	 * @param array $params
	 * @param array $extra
	 * @return Response
	 */
	public function callMethod($method, array $params = [], array $extra = [])
	{
		// Before run call need stop session
		if ($this->getContainer() !== null) {
			$this->getContainer()->get('session')->save();
		}

		// Call method
		$response = $this->client->call($method, $params, $extra);

		// After run call need restart session
		if ($this->getContainer() !== null) {
			$this->getContainer()->get('session')->migrate();
		}

		return $response;
	}
}
