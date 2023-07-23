<?php

namespace Tests\Timiki\Bundle\RpcServerBundle\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timiki\Bundle\RpcServerBundle\DependencyInjection\RpcServerExtension;

class RpcServerExtensionTest extends TestCase
{
    private ContainerBuilder|null $container = null;

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
                'parameters' => [
                    'allow_extra_params' => false,
                ],
            ],
        ], $this->container);

        $this->assertTrue($this->container->has('rpc.server.json_handler.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.http_handler.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.mapper.forTestName'));
        $this->assertTrue($this->container->has('rpc.server.json_handler.default'));
        $this->assertTrue($this->container->has('rpc.server.http_handler.default'));
        $this->assertTrue($this->container->has('rpc.server.mapper.default'));
        $this->assertTrue($this->container->has('rpc.server.serializer.base'));
        $this->assertTrue($this->container->has('rpc.server.serializer.role'));

        $this->assertEquals(false, $this->container->getParameter('rpc.server.parameters.allow_extra_params'));
    }
}
