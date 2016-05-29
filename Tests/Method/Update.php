<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class Update extends Method
{
	/**
	 * Get the method params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return [
			'a' => null,
			'b' => null,
			'c' => null,
			'd' => null,
			'e' => null,
		];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		// Notification
	}
}
