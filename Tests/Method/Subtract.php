<?php

namespace Timiki\Bundle\RpcServerBundle\Tests\Method;

use Timiki\Bundle\RpcServerBundle\Rpc\Method;

class Subtract extends Method
{
	/**
	 * Get the method params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return [
			['subtrahend', null, 0],
			['minuend', null, 0],
		];
	}

	/**
	 * Execute the server method
	 */
	public function execute()
	{
		$this->result($this->getValue('subtrahend') - $this->getValue('minuend'));
	}
}
