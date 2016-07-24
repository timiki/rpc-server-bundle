<?php

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
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

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
				->variableNode('path')
					->info('Array paths or path for find RPC methods class. If it empty RPC server will try find RPC methods in all bundle in directory "Method".')
					->defaultValue(null)
				->end()
				->scalarNode('cache')
					->defaultValue(null)
					->info('Id cache service. Cache service must be instance of "Doctrine\Common\Cache\CacheProvider". By default use file cache in %kernel.cache_dir%/rpc')
				->end()
                ->arrayNode('proxy')
                    ->addDefaultsIfNotSet(['enable'=>false,'address' => []])
                    ->children()
                        ->variableNode('enable')->defaultValue(false)->end()
                        ->variableNode('address')->defaultValue([])->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
