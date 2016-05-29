<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class NotifyHello extends Method
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
		];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		$this->result('Hello');
	}
}
