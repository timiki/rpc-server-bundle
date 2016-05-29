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
			'a' => null,
			'b' => null,
			'c' => null,
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
