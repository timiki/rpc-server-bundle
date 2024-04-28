<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;

class JsonEncodeFlagsTest extends WebTestCase
{
    protected function setUp(): void
    {
        self::createClient();
    }

    public function test(): void
    {
        /** @var HttpHandler $httpHandler */
        $httpHandler = self::$container->get('rpc.server.http_handler.v1');
        $httpHandler->setJsonEncodeFlags(0);

        $data = [
            'jsonrpc' => '2.0',
            'method' => 'ping',
            'params' => ['param' => '/тест'],
            'id' => 1,
        ];

        $httpRequest = HttpRequest::create('/v1', 'POST', [], [], [], [], \json_encode($data));
        $response = $httpHandler->handleHttpRequest($httpRequest);

        self::assertSame(
            '{"jsonrpc":"2.0","result":["pong","\/\u0442\u0435\u0441\u0442"],"id":1}',
            $response->getContent()
        );

        $httpHandler->setJsonEncodeFlags(\JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);

        $response = $httpHandler->handleHttpRequest($httpRequest);

        self::assertSame(
            '{"jsonrpc":"2.0","result":["pong","/тест"],"id":1}',
            $response->getContent()
        );
    }
}
