<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class GetData extends Method
{
	/**
	 * Get the method params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return [];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		$this->result(["hello", 5]);
	}
}
