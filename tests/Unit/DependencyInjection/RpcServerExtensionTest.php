<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timiki\Bundle\RpcServerBundle\DependencyInjection\RpcServerExtension;

/**
 * @see RpcServerExtension
 */
class RpcServerExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testLoadFullConfig()
    {
        $jsonEncodeFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        $extension = new RpcServerExtension();
        $extension->load([
            'rpc_server' => [
                'mapping' => [
                   'testMapping',
                   'forTestName' => 'testNameMapping',
                ],
                'error_code' => 302,
                'json_encode_flags' => $jsonEncodeFlags,
            ],
        ], $this->container);

        $this->assertTrue($this->container->has('rpc.server.json_handler.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.http_handler.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.mapper.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.json_handler.default'));
        $this->assertTrue($this->container->has('rpc.server.http_handler.default'));
        $this->assertTrue($this->container->has('rpc.server.mapper.default'));
        $this->assertTrue($this->container->has('rpc.server.cache'));
        $this->assertTrue($this->container->has('rpc.server.serializer.base'));
        $this->assertTrue($this->container->has('rpc.server.serializer.role'));

        $httpHandler = $this->container->getDefinition('rpc.server.http_handler.default');

        // check error code
        $args = $httpHandler->getArguments();
        $this->assertEquals(302, $args[1]);

        // check json encode flags
        self::assertTrue($httpHandler->hasMethodCall('setJsonEncodeFlags'));
        foreach ($httpHandler->getMethodCalls() as $call) {
            if ('setJsonEncodeFlags' !== $call[0]) {
                continue;
            }
            self::assertSame([$jsonEncodeFlags], $call[1]);
        }
    }

    public function testConfigWithoutJsonEncodeFlags()
    {
        (new RpcServerExtension())->load([
            'rpc_server' => [
                'mapping' => [
                    'testMapping',
                    'forTestName' => 'testNameMapping',
                ],
                'error_code' => 302,
            ],
        ], $this->container);

        $httpHandler = $this->container->getDefinition('rpc.server.http_handler.default');

        // check json encode flags
        self::assertFalse($httpHandler->hasMethodCall('setJsonEncodeFlags'));
    }
}
