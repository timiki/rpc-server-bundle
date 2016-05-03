<?php

namespace Timiki\Bundle\RpcServerBundle\Test;

use PHPUnit_Framework_TestCase;
use Timiki\Bundle\RpcServerBundle\Rpc\JsonRequest;

class JsonRequestTest extends PHPUnit_Framework_TestCase
{

	public function testNullRequest()
	{
		$request = new JsonRequest(null, null, null, null);

		$this->assertEquals(false, $request->isValid());
	}

	/**
	 * @depends testNullRequest
	 */
	public function testParamsStringRequest()
	{
		$request = new JsonRequest(null, null, null, 'string');

		$this->assertEquals(false, $request->isValid());
	}

	/**
	 * @depends testNullRequest
	 */
	public function testParamsArrayRequest()
	{
		$request = new JsonRequest('2.0', 1, 'string', [1, 2, 3]);

		$this->assertEquals(true, $request->isValid());
	}

	/**
	 * @depends testNullRequest
	 */
	public function testMethodNullRequest()
	{
		$request = new JsonRequest('2.0', 1, null, [1, 2, 3]);

		$this->assertEquals(false, $request->isValid());
	}

	/**
	 * @depends testNullRequest
	 */
	public function testMethodArrayRequest()
	{
		$request = new JsonRequest('2.0', 1, [1, 2, 3], []);

		$this->assertEquals(false, $request->isValid());
	}

}
