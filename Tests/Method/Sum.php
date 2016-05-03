<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class Sum extends Method
{
	/**
	 * Get the method params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return [
			['a', null, 0],
			['b', null, 0],
			['c', null, 0],
		];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		$this->result($this->getValue('a') + $this->getValue('b') + $this->getValue('c'));
	}
}
