<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use phpDocumentor\Reflection\Types\Scalar;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;
use Timiki\Bundle\RpcServerBundle\Handler\JsonHandler;
use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Timiki\Bundle\RpcServerBundle\Mapper\MapperInterface;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodInterface;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistry;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @see \Tests\Timiki\Bundle\RpcServerBundle\Unit\DependencyInjection\RpcServerExtensionTest
 */
class RpcServerExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        /**
         * @var array{
         *             mapping?: mixed,
         *             cache?: mixed,
         *             serializer?: mixed,
         *             error_code?: scalar,
         *             json_encode_flags: mixed|null,
         *             } $config
         */
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (false === isset($config['mapping'])) {
            throw new \RuntimeException(__CLASS__.': mapping can\'t be empty');
        }

        $errorCode = empty($config['error_code']) ? 200 : $config['error_code'];

        $jsonEncodeFlags = !\is_int($config['json_encode_flags'])
            ? null
            : \intval($config['json_encode_flags']);

        // Cache

        if (empty($config['cache'])) {
            $cacheId = 'rpc.server.cache';
            $cacheDefinition = new Definition(
                'Doctrine\Common\Cache\FilesystemCache',
                ['%kernel.cache_dir%/rpc', '']
            );

            $cacheDefinition->setPublic(true);
            $container->setDefinition($cacheId, $cacheDefinition);
        } else {
            $cacheId = $config['cache'];
        }

        // Serializer

        $serializerId = empty($config['serializer']) ? 'rpc.server.serializer.base' : $config['serializer'];

        // Registry

        $registry = new Definition(HttpHandlerRegistry::class);

        /**
         * Mapping.
         *
         * @param string       $name
         * @param array|string $paths
         */
        $createMapping = function ($name, $paths) use ($cacheId, $serializerId, $container, $errorCode, $jsonEncodeFlags, $registry) {
            /** @var non-empty-string $name */
            $name = empty($name)
                ? 'default'
                : $name;
            $mapperId = 'rpc.server.mapper.'.$name;

            $this->prepareMethods($mapperId, $paths, $container);

            $mapper = new Definition(Mapper::class);

            $mapper
                ->setPublic(true)
                ->addTag(MapperInterface::class); // Tag it for access from another place

            // Json Handler
            $jsonHandlerId = 'rpc.server.json_handler.'.$name;
            $jsonHandler = new Definition(JsonHandler::class, [new Reference($mapperId), new Reference($serializerId)]);

            $jsonHandler->setPublic(true);
            $jsonHandler->addMethodCall(
                'setEventDispatcher',
                [new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $jsonHandler->addMethodCall(
                'setCache',
                [new Reference($cacheId, ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $jsonHandler->addMethodCall(
                'setContainer',
                [new Reference('service_container')]
            );

            // Http handler
            $httpHandlerId = 'rpc.server.http_handler.'.$name;
            $httpHandler = new Definition(HttpHandler::class, [new Reference($jsonHandlerId), $errorCode]);

            $httpHandler->setPublic(true);
            $httpHandler->addMethodCall(
                'setEventDispatcher',
                [new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $httpHandler->addMethodCall(
                'setProfiler',
                [new Reference('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            if (\is_int($jsonEncodeFlags) && $jsonEncodeFlags >= 0) {
                /* @see HttpHandler::setJsonEncodeFlags() */
                $httpHandler->addMethodCall(
                    'setJsonEncodeFlags',
                    [$jsonEncodeFlags]
                );
            }

            // Set definitions
            $container->setDefinition($mapperId, $mapper);
            $container->setDefinition($jsonHandlerId, $jsonHandler);
            $container->setDefinition($httpHandlerId, $httpHandler);

            // Add http handler to registry
            $registry->addMethodCall('add', [$name, new Reference($httpHandlerId)]);
        };

        $mapping = $config['mapping'];

        foreach ((array) $mapping as $key => $value) {
            if (\is_numeric($key)) {
                $createMapping(null, $value);
            } elseif (\is_string($key) && !empty($value)) {
                $createMapping($key, (array) $value);
            }
        }

        $container->setDefinition(HttpHandlerRegistry::class, $registry);
    }

    /**
     * @param string                                                  $mapperId
     * @param string|string[]                                         $paths
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Exception
     */
    private function prepareMethods($mapperId, $paths, ContainerBuilder $container)
    {
        if (true === \is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $path = \realpath($path);

            if (false === \is_dir($path)) {
                continue;
            }

            // Add method path to resource
            $container->addResource(new GlobResource($path, '/*', true));
            $classes = $this->loadMethods($path, $container);

            foreach ($classes as $class) {
                if (true === $container->hasDefinition($class)) {
                    $container->getDefinition($class)
                        ->addTag(
                            MethodInterface::class,
                            [
                                'mapperId' => $mapperId,
                            ]
                        );
                    continue;
                }

                $container
                    ->register($class, $class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setPublic(true)
                    ->addTag(
                        MethodInterface::class,
                        [
                            'mapperId' => $mapperId,
                        ]
                    );
            }
        }
    }

    /**
     * @param string                                                  $path
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Exception
     *
     * @return array
     */
    private function loadMethods($path, ContainerBuilder $container)
    {
        $classes = [];

        $prefix = 'App';
        $rootPath = $container->getParameter('kernel.project_dir').'/src';
        $dir = new \DirectoryIterator($path);

        foreach ($dir as $file) {
            if ($file->isFile()) {
                try {
                    $namespace = \trim(\str_replace($rootPath, '', $file->getPath()), '/\\');
                    $class = \trim(\str_replace('.php', '', $file->getFilename()), '/\\');
                    $fullClassName = \str_replace('/', '\\', $prefix.'\\'.$namespace.'\\'.$class);

                    if (\class_exists($fullClassName, true)) {
                        $classes[] = $fullClassName;
                    } else {
                        $classesBefore = \get_declared_classes();
                        include_once $file->getPath().'/'.$file->getFilename();
                        $classesAfter = \get_declared_classes();

                        $classes = \array_merge($classes, \array_diff($classesAfter, $classesBefore));
                    }
                } catch (\Exception $e) {
                    // ignore it
                }
            }

            if ($file->isDir() && !$file->isDot()) {
                $classes = \array_merge($classes, $this->loadMethods($file->getPathname(), $container));
            }
        }

        return $classes;
    }
}
