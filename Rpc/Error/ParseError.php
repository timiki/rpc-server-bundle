<?php

namespace Timiki\Bundle\RpcServerBundle\Rpc\Error;

class ParseError extends AbstractError
{
	/**
	 * Error code
	 *
	 * @var string
	 */
	protected $code = '-32700';

	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message = 'Parse error';
}
