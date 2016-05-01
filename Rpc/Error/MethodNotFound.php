<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

class MethodNotFound extends AbstractError
{
	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $code = '-32601';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message = 'Method not found';
}
