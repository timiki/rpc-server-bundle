<?php

declare(strict_types=1);

namespace Timiki\Bundle\RpcServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rpc_server');
        $rootNode = $treeBuilder->getRootNode();

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
                    ->info('Id serializer service. By default use Timiki\Bundle\RpcServerBundle\Serializer\BaseSerializer')
                ->end()
                ->arrayNode('parameters')
                    ->children()
                        ->booleanNode('allow_extra_params')
                            ->info('Allow extra params in JSON request')
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('error_code')
                    ->setDeprecated(
                        'timiki/rpc-server-bundle',
                        '^6.1',
                        'error_code is deprecated'
                    )
                    ->info('Error response code')
                    ->defaultValue(200)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
