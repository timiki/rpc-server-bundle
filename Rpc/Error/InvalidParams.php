<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

class InternalError extends AbstractError
{
	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $code = '-32602';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message = 'Invalid params';
}
