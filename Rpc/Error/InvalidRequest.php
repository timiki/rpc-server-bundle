<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

class InvalidRequest extends AbstractError
{
	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $code = '-32600';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message = 'Invalid Request';
}
