<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timiki\Bundle\RpcServerBundle\Attribute;
use Timiki\Bundle\RpcServerBundle\Exceptions\InvalidMappingException;

class RpcMethodPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedMethods = $container->findTaggedServiceIds('rpc.server.method');
        $mapperMetaData = [];

        // Collect meta data from methods tags
        foreach ($taggedMethods as $methodId => $attrs) {
            foreach ($attrs as $attr) {
                $mapperId = $attr['mapperId'];

                if (true === isset($mapperMetaData[$mapperId][$methodId])) {
                    continue;
                }

                $mapperMetaData[$mapperId][$methodId] = $this->loadClassMetadata(
                    $container->getReflectionClass($methodId)
                );
            }
        }

        $taggedMappers = $container->findTaggedServiceIds('rpc.server.mapper');
        $methodsMetaData = [];

        // Inject method meta data to mapper
        foreach ($taggedMappers as $mapperId => $mapperAttr) {
            if (false === isset($mapperMetaData[$mapperId])) {
                continue; // Unknown mapper
            }

            $methodsMeta = $mapperMetaData[$mapperId];

            foreach ($methodsMeta as $meta) {
                if (null === $meta) {
                    continue;
                }

                $methodsMetaData[$mapperId][$meta['method']] = $meta;
            }
        }

        // Inject method meta data to mapper
        foreach ($methodsMetaData as $mapperId => $methods) {
            $definition = $container->getDefinition($mapperId);
            $definition->addMethodCall('addMethods', [$methods]);
        }
    }

    public function loadClassMetadata(\ReflectionClass $reflectionClass): array|null
    {
        $attributes = $reflectionClass->getAttributes(Attribute\Method::class);

        if (0 == count($attributes)) {
            return null;
        }

        /** @var Attribute\Method $instance */
        $instance = $attributes[0]->newInstance();
        $methodName = $instance->name;

        $meta = [];
        $meta['method'] = $methodName;
        $meta['class'] = $reflectionClass->getName();
        $meta['file'] = $reflectionClass->getFileName();
        $meta['cache'] = null;
        $meta['roles'] = null;
        $meta['execute'] = null;
        $meta['params'] = [];

        // Cache
        $attributes = $reflectionClass->getAttributes(Attribute\Cache::class);

        if (count($attributes) > 0) {
            /** @var Attribute\Cache $instance */
            $instance = $attributes[0]->newInstance();

            if (!empty($instance->lifetime)) {
                $meta['cache'] = $instance->lifetime;
            }
        }

        // Roles
        $attributes = $reflectionClass->getAttributes(Attribute\Roles::class);

        if (count($attributes) > 0) {
            /** @var Attribute\Roles $instance */
            $instance = $attributes[0]->newInstance();

            if (!empty($instance->roles)) {
                $meta['roles'] = (array) $instance->roles;
            }
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $attributes = $reflectionMethod->getAttributes(Attribute\Execute::class);
            if (count($attributes) > 0) {
                $meta['execute'] = $reflectionMethod->name;
            }
        }

        if (null === $meta['execute'] && $reflectionClass->hasMethod('__invoke')) {
            $meta['execute'] = '__invoke';
        }

        if (null === $meta['execute']) {
            throw new InvalidMappingException(\sprintf('Class "%s" need have method with @Execute attribute or must have __invoke method', $reflectionClass->getName()));
        }

        // Params
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes(Attribute\Param::class);

            if (count($attributes) > 0) {
                $meta['params'][$reflectionProperty->name] = $reflectionProperty->getDefaultValue();
            }
        }

        return $meta;
    }
}
