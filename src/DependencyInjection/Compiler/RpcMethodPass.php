<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException;
use Timiki\Bundle\RpcServerBundle\Mapper\MapperInterface;
use Timiki\Bundle\RpcServerBundle\Mapper\MethodInterface;
use Timiki\Bundle\RpcServerBundle\Mapping;

/**
 * Class RpcMethodPass add methods to mappers.
 */
class RpcMethodPass implements CompilerPassInterface
{
    private $reader;

    private $methodMeta = [];

    /**
     * RpcMethodPass constructor.
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct()
    {
        $this->reader = new AnnotationReader(new DocParser());
        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidMappingException
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $taggedMethods = $container->findTaggedServiceIds(MethodInterface::class);

        // Collect meta data from methods tags
        foreach ($taggedMethods as $methodId => $attrs) {
            foreach ($attrs as $attr) {
                $cacheForMapperId = $attr['mapperId'];

                if (true === isset($this->methodMeta[$cacheForMapperId][$methodId])) {
                    continue;
                }

                $this->methodMeta[$cacheForMapperId][$methodId] = $this->loadClassMetadata(
                    $container->getReflectionClass($methodId)
                );
            }
        }

        $taggedMappers = $container->findTaggedServiceIds(MapperInterface::class);

        $methodsMetaData = [];
        // Inject method meta data to mapper
        foreach ($taggedMappers as $mapperId => $mapperAttr) {
            if (false === isset($this->methodMeta[$mapperId])) {
                continue; // Method not exists
            }

            $methodsMeta = $this->methodMeta[$mapperId];

            foreach ($methodsMeta as $methodId => $meta) {
                if (null === $meta) {
                    continue;
                }

                $methodsMetaData[$mapperId][$meta['method']] = [
                    $methodId,
                    $meta['executeMethod'],
                    $meta['params'],
                    $meta['cache'],
                    $meta['roles'],
                    $meta['cache'],
                ];
            }
        }

        // Inject method meta data to mapper
        foreach ($methodsMetaData as $mapperId => $methods) {
            $definition = $container->getDefinition($mapperId);
            $definition->addMethodCall('addMethods', [$methods]);
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @throws InvalidMappingException
     *
     * @return null|array
     */
    public function loadClassMetadata(\ReflectionClass $reflectionClass): ?array
    {
        $method = $this->reader->getClassAnnotation($reflectionClass, Mapping\Method::class);

        if (null === $method) {
            return null;
        }

        $meta = [];

        if (empty($method->value)) {
            throw new InvalidMappingException(
                \sprintf(
                    '@Method annotation must have name in class "%s", @Method("method name")',
                    $reflectionClass->getName()
                )
            );
        }

        $meta['method'] = $method->value;
        $meta['class'] = $reflectionClass->getName();
        $meta['file'] = $reflectionClass->getFileName();

        // Cache
        $meta['cache'] = null;
        $cache = $this->reader->getClassAnnotation($reflectionClass, Mapping\Cache::class);
        /** @var Mapping\Cache $cache */
        if (null !== $cache) {
            $meta['cache'] = (int) $cache->lifetime;
        }

        // Roles
        $meta['roles'] = null;
        $roles = $this->reader->getClassAnnotation($reflectionClass, Mapping\Roles::class);
        /** @var Mapping\Roles $roles */
        if (null !== $roles) {
            $meta['roles'] = (array) $roles->value;
        }

        // Method execute. On in class
        $meta['executeMethod'] = null;

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($paramMeta = $this->reader->getMethodAnnotation($reflectionMethod, Mapping\Execute::class)) {
                $meta['executeMethod'] = $reflectionMethod->name;
            }
        }

        if (empty($meta['executeMethod'])) {
            throw new InvalidMappingException(
                \sprintf(
                    'Method need have @Execute annotation in class "%s", @Execute()',
                    $reflectionClass->getName()
                )
            );
        }

        // Params
        $meta['params'] = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($paramMeta = $this->reader->getPropertyAnnotation($reflectionProperty, Mapping\Param::class)) {
                $meta['params'][$reflectionProperty->name] = true;
            }
        }

        return $meta;
    }
}
