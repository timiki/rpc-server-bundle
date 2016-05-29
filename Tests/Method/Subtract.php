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
			'subtrahend' => null,
			'minuend'    => null,
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
