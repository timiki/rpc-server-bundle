<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Method
 */
abstract class Method
{
	/**
	 * The handler instance
	 *
	 * @var Handler
	 */
	protected $handler;

	/**
	 * Params values
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * result
	 */
	protected $result = null;

	/**
	 * error
	 */
	protected $error = [];

	/**
	 * Get granted roles
	 *
	 * @return array
	 */
	public function getRoles()
	{
		//
		// Example code:
		//
		//  return [
		//      ['user', 'admin']
		//  ];
		//

		return [];
	}

	/**
	 * Get the method params
	 *
	 * @return array
	 */
	public function getParams()
	{
		//
		// Example code:
		//
		//  return [
		//      ['param name'] // required by default
		//  ];
		//
		//  return [
		//      ['param name', 'validate rule', 'default value']
		//      ......
		//  ];
		//

		return [];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		// Your code
	}

	/**
	 * Set new values
	 *
	 * @param array $values
	 * @return $this
	 */
	public function setValues(array $values)
	{
		$this->values = [];

		// Given only values

		if (array_keys($values) === range(0, count($values) - 1)) {

			$params = $this->getParams();

			foreach ($values as $key => $value) {
				if (isset($params[$key])) {
					$values[$params[$key][0]] = $value;
				}
			}

		}

		// Process values

		foreach ($this->getParams() as $param) {

			if (array_key_exists($param[0], $values)) {
				$this->values[$param[0]] = $values[$param[0]];
			} else {
				if (isset($param[2])) {
					$this->values[$param[0]] = $param[2];
				}
			}

		}

		return $this;
	}


	/**
	 * Get param value
	 *
	 * @param string $name Param name
	 * @return mixed|null
	 */
	public function getValue($name)
	{
		if (array_key_exists($name, $this->values)) {
			return $this->values[$name];
		}

		return null;
	}

	/**
	 * Get params values
	 *
	 * @return array
	 */
	public function getValues()
	{
		return (array)$this->values;
	}

	/**
	 * Gets a service
	 *
	 * @param     $id
	 * @param int $invalidBehavior
	 * @return object|null
	 */
	public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
	{
		if ($this->handler->getContainer()) {
			return $this->handler->getContainer()->get($id, $invalidBehavior);
		}

		return null;
	}

	/**
	 * Get container instance
	 *
	 * @return ContainerInterface|null
	 */
	public function getContainer()
	{
		return $this->handler->getContainer();
	}

	/**
	 * Translates the given message
	 *
	 * @param       $id
	 * @param array $parameters
	 * @param null  $domain
	 * @param null  $locale
	 * @return string
	 */
	public function trans($id, array $parameters = [], $domain = null, $locale = null)
	{
		if ($this->getContainer()) {
			return $this->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
		}

		return $id;
	}

	/**
	 * Translates the given choice message by choosing a translation according to a number
	 *
	 * @param       $id
	 * @param       $number
	 * @param array $parameters
	 * @param null  $domain
	 * @param null  $locale
	 * @return string
	 */
	public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
	{
		if ($this->getContainer()) {
			return $this->getContainer()->get('translator')->transChoice($id, $number, $parameters, $domain, $locale);
		}

		return $id;
	}

	/**
	 * Get the method name
	 *
	 * @return string|null
	 */
	public function getName()
	{
		$className = get_class($this);
		$className = explode('\\', $className);

		return $className[count($className) - 1];
	}

	/**
	 * Get header
	 *
	 * @return Handler
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Set header
	 *
	 * @param Handler $handler
	 * @return $this
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Set result value
	 *
	 * @param mixed $result
	 * @return $this
	 */
	public function result($result)
	{
		$this->result = $result;

		return $this;
	}

	/**
	 * Set result error
	 *
	 * @param mixed $error
	 * @return $this
	 */
	public function error($error)
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
		return !empty($this->result);
	}

	/**
	 * Is set error
	 *
	 * @return boolean
	 */
	public function isError()
	{
		return count($this->error) > 0;
	}

	/**
	 * Get errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->error;
	}

	/**
	 * Get result value
	 *
	 * @return mixed|null
	 */
	public function getResult()
	{
		return $this->result;
	}
}
