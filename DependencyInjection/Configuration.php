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
                ->variableNode('type')
                    ->defaultValue('json')
                ->end()
                ->arrayNode('proxy')
                    ->addDefaultsIfNotSet(['enable'=>false,'type'=>'json','address' => [],'forwardHeaders'=>[],'forwardCookies'=>[],'forwardCookiesDomain'=> '', 'extra'=>[], 'headers'=>[], 'cookies'=>[]])
                    ->children()
                        ->variableNode('enable')->defaultValue(false)->end()
                        ->variableNode('type')->defaultValue('json')->end()
                        ->variableNode('address')->defaultValue([])->end()
                        ->variableNode('forwardHeaders')->defaultValue([])->end()
                        ->variableNode('forwardCookies')->defaultValue([])->end()
                        ->variableNode('forwardCookiesDomain')->defaultValue('')->end()
            			->variableNode('extra')->defaultValue([])->end()
						->variableNode('headers')->defaultValue([])->end()
						->variableNode('cookies')->defaultValue([])->end()
                    ->end()
                ->end()
                ->arrayNode('paths')
                    ->defaultValue([])
                    ->prototype('array')
                        ->children()
                            ->variableNode('namespace')->isRequired()->end()
                            ->variableNode('path')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
