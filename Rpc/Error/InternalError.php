<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

class InvalidParams extends AbstractError
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
