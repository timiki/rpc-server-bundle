<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Timiki\Bundle\RpcServerBundle\DependencyInjection\RpcServerExtension;

class RpcServerExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testLoadFullConfig()
    {
        $extension = new RpcServerExtension();
        $extension->load([
            'rpc_server' => [
                'mapping' => [
                   'testMapping',
                   'forTestName' => 'testNameMapping',
                ],
                'error_code' => 302,
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

        /** @var Definition $httpHandler */
        $httpHandler = $this->container->getDefinition('rpc.server.http_handler.default');

        // check error code
        $args = $httpHandler->getArguments();

        $this->assertEquals(302, $args[1]);
    }
}
