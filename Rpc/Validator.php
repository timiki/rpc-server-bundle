<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc;

/**
 * Validator class validation method params
 */
class Validator
{
	/**
	 * Validate method params function
	 *
	 * If not found validate rule in result will be string
	 * ['param' => paramName, 'invalidValidateRule' => 'Unknown validate rule','msg' => 'Invalid validate rule']
	 *
	 * @param Method $method
	 * @param array  $params
	 * @return array
	 */
	public function validate(Method $method, $params = [])
	{
		$result = [];

		foreach ($method->getParams() as $param) {

			// param result validate
			$paramResultValidate = [];
			$rules               = isset($param[1]) ? $param[1] : '';

			if (!empty($rules)) {

				$rulesArray = explode('|', $rules);

				foreach ($rulesArray as $rule) {

					$validateMethodName = 'validate'.ucfirst(strtolower($rule));

					if (method_exists($this, $validateMethodName)) {
						if (!$this->{$validateMethodName}($param[0], $params)) {
							$paramResultValidate[] = ['param' => $param[0], 'error' => 'Validate error for rule '.strtolower($rule)];
						}
					} else {
						$paramResultValidate[] = ['param' => $param[0], 'error' => 'Invalid validate rule'];
					}
				}

				if (count($paramResultValidate) > 0) {
					$result[$param[0]] = $paramResultValidate;
				}

			}

		}

		return $result;
	}

	/**
	 * Validate that a required attribute exists
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateRequired($param, $params)
	{
		if (!array_key_exists($param, $params)) {
			return false;
		}

		return true;
	}

	/**
	 * Validate that an attribute is an array
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateArray($param, $params)
	{
		if (!array_key_exists($param, $params)) {
			return false;
		}

		return is_array($params[$param]);
	}

	/**
	 * Validate that an attribute is a boolean.
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateBoolean($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return in_array($params[$param], [true, false, 0, 1, '0', '1'], true);
		}

		return true;
	}

	/**
	 * Validate that an attribute is an integer
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateInteger($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return filter_var($params[$param], FILTER_VALIDATE_INT) !== false;
		}

		return true;
	}

	/**
	 * Validate that an attribute is numeric
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateNumeric($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return is_numeric($params[$param]);
		}

		return true;
	}

	/**
	 * Validate that an attribute is a string
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateString($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return is_string($params[$param]);
		}

		return true;
	}

	/**
	 * Validate that an attribute is a valid IP
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateIp($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return filter_var($params[$param], FILTER_VALIDATE_IP) !== false;
		}

		return true;
	}

	/**
	 * Validate that an attribute is a valid e-mail address
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateEmail($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return filter_var($params[$param], FILTER_VALIDATE_EMAIL) !== false;
		}

		return true;
	}

	/**
	 * Validate that an attribute is a valid URL
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateUrl($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return filter_var($params[$param], FILTER_VALIDATE_URL) !== false;
		}

		return true;
	}

	/**
	 * Validate that an attribute contains only alphabetic characters
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateAlpha($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return preg_match('/^[\pL\pM]+$/u', $params[$param]);
		}

		return true;
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateAlphaNum($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return preg_match('/^[\pL\pM\pN]+$/u', $params[$param]);
		}

		return true;
	}

	/**
	 * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateAlphaDash($param, $params)
	{
		if (array_key_exists($param, $params)) {
			return preg_match('/^[\pL\pM\pN_-]+$/u', $params[$param]);
		}

		return true;
	}

	/**
	 * Validate that an attribute was "accepted".
	 *
	 * This validation rule implies the attribute is "required".
	 *
	 * @param  string $param
	 * @param  mixed  $params
	 * @return bool
	 */
	protected function validateAccepted($param, $params)
	{
		return ($this->validateRequired($param, $params) && in_array($params[$param], ['yes', 'on', '1', 1, true, 'true'], true));
	}
}