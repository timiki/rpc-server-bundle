<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Timiki\Bundle\RpcServerBundle\Server\Exceptions\InvalidConfigException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RpcServerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        // Set handler
        $this->setHandler($container);

        // Set mapper
        $this->setMapper($config['path'], $container);

        // Set proxy
        $this->setProxy($config['proxy'], $container);

        // Set cache
        $this->setCache($config['cache'], $container);
    }

    /**
     * RPC handler.
     *
     * @param ContainerBuilder $container
     */
    public function setHandler(ContainerBuilder $container)
    {
        $handler = new Definition('Timiki\Bundle\RpcServerBundle\Server\Handler');

        $handler->addMethodCall('setContainer', [new Reference('service_container')]);

        $container->setDefinition('rpc.server.handler', $handler);
    }

    /**
     * RPC mapping.
     *
     * @param array            $mapping
     * @param ContainerBuilder $container
     */
    public function setMapper($mapping, ContainerBuilder $container)
    {
        $mapper = new Definition('Timiki\Bundle\RpcServerBundle\Server\Mapper',
            [$container->getParameter('kernel.debug')]);

        if (empty($mapping)) {
            $mapping = [];
            foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
                $mapping[] = '@'.$bundle.'/Method';
            }
        }

        // Add path to mapper for mapping RPC methods
        foreach ($mapping as $path) {
            $mapper->addMethodCall('addPath', [$path]);
        }

        $mapper->addMethodCall('setContainer', [new Reference('service_container')]);

        $container->setDefinition('rpc.server.mapper', $mapper);

        $handler = $container->getDefinition('rpc.server.handler');
        $handler->addMethodCall('setMapper', [new Reference('rpc.server.mapper')]);

    }

    /**
     * RPC proxy.
     *
     * @param                  $configs
     * @param ContainerBuilder $container
     */
    public function setProxy($configs, ContainerBuilder $container)
    {
        if ($configs['enable']) {

            $handler = $container->getDefinition('rpc.server.handler');

            $proxy = new Definition(
                'Timiki\Bundle\RpcServerBundle\Server\Proxy',
                [
                    $configs['address'],
                    new Reference('rpc.server.handler'),
                ]);

            $proxy->addMethodCall('setContainer', [new Reference('service_container')]);

            $container->setDefinition('rpc.server.proxy', $proxy);

            $handler->addMethodCall('setProxy', [new Reference('rpc.server.proxy')]);

        }
    }

    /**
     * RPC cache driver.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws InvalidConfigException
     */
    public function setCache($configs, ContainerBuilder $container)
    {

        if (empty($configs)) {

            $cache = new Definition('Doctrine\Common\Cache\FilesystemCache', ['%kernel.cache_dir%/rpc', '']);
            $container->setDefinition('rpc.server.cache', $cache);

            $configs = 'rpc.server.cache';

        }

        $mapper  = $container->getDefinition('rpc.server.mapper');
        $handler = $container->getDefinition('rpc.server.handler');

        $mapper->addMethodCall('setCache', [new Reference($configs)]);
        $handler->addMethodCall('setCache', [new Reference($configs)]);
    }

}
