<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $errorCode = empty($config['error_code']) ? 200 : $config['error_code'];

        /**
         * Cache
         */

        $cacheId = empty($config['cache']) ? 'rpc.server.cache' : $config['cache'];

        if (!$container->hasDefinition($cacheId)) {
            $cacheDefinition = new Definition(
                'Doctrine\Common\Cache\FilesystemCache',
                ['%kernel.cache_dir%/rpc', '',]
            );

            $cacheDefinition->setPublic(true);
            $container->setDefinition($cacheId, $cacheDefinition);
        }

        /**
         * Serializer
         */

        $serializerId = empty($config['serializer']) ? 'rpc.server.serializer' : $config['serializer'];

        if (!$container->hasDefinition($serializerId)) {
            $serializerDefinition = new Definition(
                'Timiki\Bundle\RpcServerBundle\Serializer\BaseSerializer',
                [new Reference('serializer', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $serializerDefinition->setPublic(true);
            $container->setDefinition($serializerId, $serializerDefinition);
        }

        /**
         * Mapping
         *
         * @param $name
         * @param $paths
         */

        $createMapping = function ($name, $paths) use ($cacheId, $serializerId, $container, $errorCode) {
            // Mapper
            $mapperId = empty($name) ? 'rpc.server.mapper' : 'rpc.server.mapper.'.$name;
            $mapper = new Definition('Timiki\Bundle\RpcServerBundle\Mapper\Mapper');

            $mapper->setPublic(true);
            $mapper->addMethodCall(
                'setKernel',
                [new Reference('kernel', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $mapper->addMethodCall(
                'setStopwatch',
                [new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $mapper->addMethodCall(
                'setCache',
                [new Reference($cacheId, ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            // Add path to mapper for mapping RPC methods
            foreach ($paths as $path) {
                $mapper->addMethodCall('addPath', [$path]);
            }

            // Json Handler

            $jsonHandlerId = empty($name) ? 'rpc.server.json_handler' : 'rpc.server.json_handler.'.$name;
            $jsonHandler = new Definition(
                'Timiki\Bundle\RpcServerBundle\Handler\JsonHandler',
                [
                    new Reference($mapperId),
                    new Reference($serializerId),
                ]
            );

            $jsonHandler->setPublic(true);
            $jsonHandler->addMethodCall(
                'setEventDispatcher',
                [new Reference('rpc.server.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $jsonHandler->addMethodCall(
                'setCache',
                [new Reference($cacheId, ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $jsonHandler->addMethodCall(
                'setContainer',
                [new Reference('service_container')]
            );

            $mapper->addMethodCall(
                'setStopwatch',
                [new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            // Http handler

            $httpHandlerId = empty($name) ? 'rpc.server.http_handler' : 'rpc.server.http_handler.'.$name;
            $httpHandler = new Definition(
                'Timiki\Bundle\RpcServerBundle\Handler\HttpHandler', [new Reference($jsonHandlerId), $errorCode]
            );

            $httpHandler->setPublic(true);
            $httpHandler->addMethodCall(
                'setEventDispatcher',
                [new Reference('rpc.server.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $httpHandler->addMethodCall(
                'setProfiler',
                [new Reference('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            // Set definitions
            $container->setDefinition($mapperId, $mapper);
            $container->setDefinition($jsonHandlerId, $jsonHandler);
            $container->setDefinition($httpHandlerId, $httpHandler);
        };

        $defaultMapping = [];
        $mapping = $config['mapping'];

        if (empty($mapping)) {
            $mapping = [];
            foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
                $mapping[] = '@'.$bundle.'/Method';
            }
        }

        foreach ((array)$mapping as $key => $value) {
            if (is_numeric($key)) {
                $defaultMapping[] = $value;
            } elseif (is_string($key) && !empty($value)) {
                $createMapping($key, (array)$value);
            }
        }

        if (!empty($defaultMapping)) {
            $createMapping(null, (array)$defaultMapping);
        }
    }
}
