<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RPC Manager
 */
class Manager
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
	 * Create new instance of JSON-RPC manager
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
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Add namespace
	 *
	 * @param string $namespace Method namespace
	 * @return $this
	 */
	public function addNamespace($namespace)
	{
		$this->namespace[] = $namespace;

		return $this;
	}

	/**
	 * Add method
	 *
	 * @param string $name  Method name
	 * @param string $class Method class
	 * @return $this
	 */
	public function addMethod($name, $class)
	{
		$this->methods[$name] = $class;

		return $this;
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
				$methodObject = new $class($this->container);

				return $methodObject;
			}

		}

		foreach ($this->namespace as $namespace) {

			$class = $namespace.'\\'.$method;
			try {

				if (class_exists($class)) {
					/* @var Method $methodObject */
					$methodObject = new $class($this->container);

					return $methodObject;
				}

			} catch (\Exception $e) {
			}

		}

		return null;
	}
}
