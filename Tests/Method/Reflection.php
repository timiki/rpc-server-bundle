<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class Reflection extends Method
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
			'b' => null
		];
	}

	/**
	 * Execute the server method
	 */
	public function execute($a, $b)
	{
		return $a + $b;
	}
}
