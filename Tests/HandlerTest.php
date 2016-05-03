<?php

namespace Timiki\Bundle\RpcServerBundle\Test;

use PHPUnit_Framework_TestCase;
use Timiki\Bundle\RpcServerBundle\Rpc\Handler;
use Symfony\Component\HttpFoundation\Request;

class HandlerTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @return Handler
	 */
	public function getHandler()
	{
		$methods = [
			'subtract'     => \Timiki\Bundle\RpcServerBundle\Tests\Method\Subtract::class,
			'update'       => \Timiki\Bundle\RpcServerBundle\Tests\Method\Update::class,
			'sum'          => \Timiki\Bundle\RpcServerBundle\Tests\Method\Sum::class,
			'notify_hello' => \Timiki\Bundle\RpcServerBundle\Tests\Method\NotifyHello::class,
			'get_data'     => \Timiki\Bundle\RpcServerBundle\Tests\Method\GetData::class,
		];

		$namespace = [];

		return new Handler($methods, $namespace);
	}

	/**
	 * @return Request
	 */
	public function getHttpRequest($json)
	{
		$query      = array();
		$request    = array();
		$attributes = array();
		$cookies    = array();
		$files      = array();
		$server     = array();
		$content    = $json;

		return new Request($query, $request, $attributes, $cookies, $files, $server, $content);
	}

	public function testFindMethod()
	{
		$handler = $this->getHandler();

		$this->assertInstanceOf('Timiki\Bundle\RpcServerBundle\Rpc\Method', $handler->getMethod('subtract'));
	}

	public function testHttpRequest_1()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","result":19,"id":1}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_2()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 1}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_3()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 1}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_4()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 1}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_5()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_6()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "foobar", "id": "1"}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","error":{"code":"-32601","message":"Method not found"},"id":"1"}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_7()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error"},"id":null}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_8()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('{"jsonrpc": "2.0", "method": 1, "params": "bar"}');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_9()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"}, {"jsonrpc": "2.0", "method"]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","error":{"code":"-32700","message":"Parse error"},"id":null}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_10()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('[]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null}', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_11()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('[1]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('[{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null}]', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_12()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('[1,2,3]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('[{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null}]', $response->getContent(), $response->getContent());
	}

	public function testHttpRequest_13()
	{
		$handler  = $this->getHandler();
		$request  = $this->getHttpRequest('[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},{"foo": "boo"},{"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},{"jsonrpc": "2.0", "method": "get_data", "id": "9"}]');
		$response = $handler->handleHttpRequest($request);

		$this->assertEquals('[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","error":{"code":"-32600","message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":"-32601","message":"Method not found"},"id":"5"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]', $response->getContent(), $response->getContent());
	}
}
