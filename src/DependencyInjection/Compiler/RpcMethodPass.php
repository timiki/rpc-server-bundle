<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timiki\Bundle\RpcServerBundle\Attribute;
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
     * @throws InvalidMappingException
     */
    public function loadClassMetadata(\ReflectionClass $reflectionClass): ?array
    {
        $methodName = null;
        $method = $this->reader->getClassAnnotation($reflectionClass, Mapping\Method::class);

        if ($method) {
            if (empty($method->value)) {
                throw new InvalidMappingException(\sprintf('@Method annotation must have name in class "%s", @Method("method name")', $reflectionClass->getName()));
            }

            $methodName = $method->value;
        }

        if (null == $methodName) {
            $attributes = $reflectionClass->getAttributes(Attribute\Method::class);

            if (count($attributes) > 0) {
                /** @var Attribute\Method $instance */
                $instance = $attributes[0]->newInstance();
                $methodName = $instance->name;
            }
        }

        if (empty($methodName)) {
            return null;
        }

        $meta = [];
        $meta['method'] = $methodName;
        $meta['class'] = $reflectionClass->getName();
        $meta['file'] = $reflectionClass->getFileName();

        // Cache
        $meta['cache'] = null;
        $cache = $this->reader->getClassAnnotation($reflectionClass, Mapping\Cache::class);
        /** @var Mapping\Cache $cache */
        if (null !== $cache) {
            $meta['cache'] = (int) $cache->lifetime;
        }

        $attributes = $reflectionClass->getAttributes(Attribute\Cache::class);
        if (count($attributes) > 0) {
            /** @var Attribute\Cache $instance */
            $instance = $attributes[0]->newInstance();

            if (!empty($instance->lifetime)) {
                $meta['cache'] = $instance->lifetime;
            }
        }

        // Roles
        $meta['roles'] = null;
        $roles = $this->reader->getClassAnnotation($reflectionClass, Mapping\Roles::class);
        /** @var Mapping\Roles $roles */
        if (null !== $roles) {
            $meta['roles'] = (array) $roles->value;
        }

        $attributes = $reflectionClass->getAttributes(Attribute\Roles::class);
        if (count($attributes) > 0) {
            /** @var Attribute\Roles $instance */
            $instance = $attributes[0]->newInstance();

            if (!empty($instance->roles)) {
                $meta['roles'] = (array) $instance->roles;
            }
        }

        // Method execute. On in class
        $meta['executeMethod'] = null;

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($paramMeta = $this->reader->getMethodAnnotation($reflectionMethod, Mapping\Execute::class)) {
                $meta['executeMethod'] = $reflectionMethod->name;
            }

            $attributes = $reflectionMethod->getAttributes(Attribute\Execute::class);
            if (count($attributes) > 0) {
                $meta['executeMethod'] = $reflectionMethod->name;
            }
        }

        if (empty($meta['executeMethod'])) {
            throw new InvalidMappingException(\sprintf('Method need have @Execute annotation in class "%s", @Execute()', $reflectionClass->getName()));
        }

        // Params
        $meta['params'] = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($paramMeta = $this->reader->getPropertyAnnotation($reflectionProperty, Mapping\Param::class)) {
                $meta['params'][$reflectionProperty->name] = true;
            }

            $attributes = $reflectionProperty->getAttributes(Attribute\Param::class);
            if (count($attributes) > 0) {
                $meta['params'][$reflectionProperty->name] = true;
            }
        }

        return $meta;
    }
}
