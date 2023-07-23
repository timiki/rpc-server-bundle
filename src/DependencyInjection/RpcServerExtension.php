<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Timiki\Bundle\RpcServerBundle\EventSubscriber\CacheSubscriber;
use Timiki\Bundle\RpcServerBundle\Handler\HttpHandler;
use Timiki\Bundle\RpcServerBundle\Handler\JsonHandler;
use Timiki\Bundle\RpcServerBundle\Mapper\Mapper;
use Timiki\Bundle\RpcServerBundle\Registry\HttpHandlerRegistryInterface;
use Timiki\Bundle\RpcServerBundle\Serializer\BaseSerializer;
use Timiki\Bundle\RpcServerBundle\Serializer\SerializerInterface;

class RpcServerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $parameters = $config['parameters'] ?? [];

        // Configure cache

        if ($config['cache']) {
            $cacheDefinition = new Definition(CacheSubscriber::class, [new Reference($config['cache'])]);
            $cacheDefinition->addTag('kernel.event_subscriber');

            $container->setDefinition(CacheSubscriber::class, $cacheDefinition);
        }

        // Configure serializer

        $serializerId = $config['serializer'] ?? BaseSerializer::class;

        $container->setAlias(SerializerInterface::class, $serializerId);

        // Configure registry

        $registry = $container->getDefinition(HttpHandlerRegistryInterface::class);

        // Configure parameters

        $container->setParameter('rpc.server.parameters.allow_extra_params', $parameters['allow_extra_params'] ?? false);

        // Configure mapping

        $createMapping = function (string|null $name, array|string $paths) use ($serializerId, $container, $registry) {
            $name = empty($name) ? 'default' : $name;
            $mapperId = 'rpc.server.mapper.'.$name;

            $this->prepareMethods($mapperId, $paths, $container);

            $mapper = new Definition(Mapper::class);

            $mapper
                ->setPublic(true)
                ->addTag('rpc.server.mapper'); // Tag it for access from another place

            // Json Handler
            $jsonHandlerId = 'rpc.server.json_handler.'.$name;
            $jsonHandler = new Definition(JsonHandler::class, [new Reference($mapperId)]);

            $jsonHandler->setPublic(true);
            $jsonHandler->addMethodCall(
                'setEventDispatcher',
                [new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)]
            );

            $jsonHandler->addMethodCall(
                'setContainer',
                [new Reference('service_container')]
            );

            // Http handler
            $httpHandlerId = 'rpc.server.http_handler.'.$name;
            $httpHandler = new Definition(HttpHandler::class, [new Reference($jsonHandlerId), new Reference($serializerId)]);

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

        $mapping = $config['mapping'] ?? [];

        foreach ((array) $mapping as $key => $value) {
            if (\is_numeric($key)) {
                $createMapping(null, $value);
            } elseif (\is_string($key) && !empty($value)) {
                $createMapping($key, (array) $value);
            }
        }
    }

    /**
     * @param string|array<string> $paths
     */
    private function prepareMethods(string $mapperId, array|string $paths, ContainerBuilder $container): void
    {
        if (true === \is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $path = \realpath($path);

            if (!$path || !\is_dir($path)) {
                continue;
            }

            // Add method path to resource
            $container->addResource(new GlobResource($path, '/*', true));
            $classes = $this->loadMethods($path, $container);

            foreach ($classes as $class) {
                if (true === $container->hasDefinition($class)) {
                    $container
                        ->getDefinition($class)
                        ->addTag(
                            'rpc.server.method',
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
                        'rpc.server.method',
                        [
                            'mapperId' => $mapperId,
                        ]
                    );
            }
        }
    }

    /**
     * @return array<string>
     */
    private function loadMethods(string $path, ContainerBuilder $container): array
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
