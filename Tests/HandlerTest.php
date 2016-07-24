<?php

namespace Timiki\Bundle\RpcServerBundle\Tests;

use PHPUnit_Framework_TestCase;

class HandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHttpRequest_1()
    {
        $handler = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}');

        $response = $handler->handleHttpRequest($request);

        self::assertEquals('{"jsonrpc":"2.0","result":19,"id":1}', $response->getContent());
    }

    public function testHttpRequest_2()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_3()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_4()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 1}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","result":-19,"id":1}', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_5()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('', $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_6()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "foobar", "id": "1"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found","data":"foobar"},"id":"1"}',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_7()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_8()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": 1, "params": "bar"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_9()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"}, {"jsonrpc": "2.0", "method"]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_10()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('[]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_11()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('[1]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_12()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('[1,2,3]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_13()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('[{"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},{"foo": "boo"},{"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},{"jsonrpc": "2.0", "method": "get_data", "id": "9"}]');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found","data":"foo.get"},"id":"5"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]',
            $response->getContent(), $response->getContent());
    }

    public function testHttpRequest_14()
    {
        $handler  = Utils::getHandler(__DIR__.DIRECTORY_SEPARATOR.'Method');
        $request  = Utils::getHttpRequest('{"jsonrpc": "2.0", "method": "get_error", "id": "1"}');
        $response = $handler->handleHttpRequest($request);

        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32002,"message":"Method exception","data":"Exception data"},"id":"1"}',
            $response->getContent(), $response->getContent());
    }
}
