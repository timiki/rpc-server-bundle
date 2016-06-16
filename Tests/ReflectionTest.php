<?php

namespace Timiki\Bundle\RpcServerBundle\Test;

use PHPUnit_Framework_TestCase;
use Timiki\Bundle\RpcServerBundle\Rpc\Handler;
use Timiki\Bundle\RpcServerBundle\Rpc\Manager;
use Symfony\Component\HttpFoundation\Request;

class ReflectionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @return Handler
	 */
	public function getHandler()
	{
		$methods = [
			'reflection' => \Timiki\Bundle\RpcServerBundle\Tests\Method\Reflection::class,
		];

		$namespace = [];

		return new Handler(new Manager($methods, $namespace));
	}

	/**
	 * @return Request
	 */
	public function getHttpRequest($json)
	{
		$query      = [];
		$request    = [];
		$attributes = [];
		$cookies    = [];
		$files      = [];
		$server     = [];
		$content    = $json;

		return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
	}

	public function testReflection()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "reflection", "params": {"a":1, "b":1}, "id": 1}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","result":2,"id":1}', $response->getContent(), $response->getContent());
	}
}
