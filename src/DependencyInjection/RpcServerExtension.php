<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

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
 */
class RpcServerExtension extends Extension
{
    private $loadedMethodPath = [];

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (false === isset($config['mapping'])) {
            throw new \RuntimeException(__CLASS__.': mapping can\'t be empty');
        }

        $errorCode = empty($config['error_code']) ? 200 : $config['error_code'];

        /**
         * Cache.
         */
        $cacheId = empty($config['cache']) ? 'rpc.server.cache' : $config['cache'];

        if (!$container->hasDefinition($cacheId)) {
            $cacheDefinition = new Definition(
                'Doctrine\Common\Cache\FilesystemCache',
                ['%kernel.cache_dir%/rpc', '']
            );

            $cacheDefinition->setPublic(true);
            $container->setDefinition($cacheId, $cacheDefinition);
        }

        /**
         * Serializer.
         */
        $serializerId = empty($config['serializer']) ? 'rpc.server.serializer.base' : $config['serializer'];

        if (!$container->hasDefinition($serializerId)) {
            $serializerId = 'rpc.server.serializer.base';
        }

        /**
         * Registry.
         */
        $registry = new Definition(HttpHandlerRegistry::class);

        /**
         * Mapping.
         *
         * @param string       $name
         * @param array|string $paths
         */
        $createMapping = function ($name, $paths) use ($cacheId, $serializerId, $container, $errorCode, $registry) {
            $name = empty($name) ? 'default' : $name;
            $mapperId = 'rpc.server.mapper.'.$name;

            $this->prepareMethods($mapperId, $paths, $container);

            $mapper = new Definition(Mapper::class);

            $mapper
                ->setPublic(true)
                ->addTag(MapperInterface::class); // Tag it for access from another place

            // Json Handler
            $jsonHandlerId = empty($name) ? 'rpc.server.json_handler' : 'rpc.server.json_handler.'.$name;
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
            $httpHandlerId = empty($name) ? 'rpc.server.http_handler' : 'rpc.server.http_handler.'.$name;
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

            $this->loadMethods($path, $container);

            if (false === isset($this->loadedMethodPath[$path]['classes'])) {
                continue;
            }

            foreach ($this->loadedMethodPath[$path]['classes'] as $class) {
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

                $container->register($class, $class)
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
     */
    private function loadMethods($path, ContainerBuilder $container)
    {
        if (true === isset($this->loadedMethodPath[$path])) {
            return; // Already loaded
        }

        $dir = new \DirectoryIterator($path);
        $loaderPhp = new Loader\PhpFileLoader($container, new FileLocator($path));

        foreach ($dir as $file) {
            if ($file->isFile()) {
                $classesBefore = \get_declared_classes();

                $loaderPhp->load($file->getFilename());

                $classesAfter = \get_declared_classes();

                $diff = \array_diff($classesAfter, $classesBefore);

                // Mark as loaded.
                $classes = $this->loadedMethodPath[$path]['classes'] ?? [];
                // merge all classes to parent
                $this->loadedMethodPath[$path]['classes'] = \array_merge($classes, $diff ?? []);
            }

            if ($file->isDir() && !$file->isDot()) {
                $classesBefore = \get_declared_classes();

                $this->loadMethods($file->getPathname(), $container);

                $classesAfter = \get_declared_classes();

                $diff = \array_diff($classesAfter, $classesBefore);

                $classes = $this->loadedMethodPath[$path]['classes'] ?? [];
                // merge all classes to parent
                $this->loadedMethodPath[$path]['classes'] = \array_merge($classes, $diff ?? []);
            }
        }
    }
}
