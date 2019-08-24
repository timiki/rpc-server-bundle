<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rpc_server');

        $rootNode
            ->children()
                ->variableNode('mapping')
                    ->info('Array paths or path for find RPC methods class. If it empty RPC server will try find RPC methods in all bundle in directory "Method".')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('cache')
                    ->defaultValue(null)
                    ->info('Id cache service. Cache service must be instance of "Doctrine\Common\Cache\Cache". By default use file cache in %kernel.cache_dir%/rpc')
                ->end()
                ->scalarNode('serializer')
                    ->defaultValue(null)
                    ->info('Id serializer service. By default use Symfony serializer')
                ->end()
                ->scalarNode('error_code')
                    ->info('Error response code')
                    ->defaultValue(200)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
