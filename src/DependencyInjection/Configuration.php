<?php

namespace Pyrowman\PheanstalkBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('pheanstalk');
        $rootNode = $treeBuilder->getRootNode()->children();

        $rootNode
            ->arrayNode('profiler')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                    ->scalarNode('template')->defaultValue('@Pheanstalk/Profiler/pheanstalk.html.twig')->end()
                ->end()
            ->end()
            ->arrayNode('pheanstalks')
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('server')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('port')
                            ->cannotBeEmpty()
                            ->defaultValue('5000')
                        ->end()
                        ->scalarNode('user')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('password')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('timeout')
                            ->cannotBeEmpty()
                            ->defaultValue('60')
                        ->end()
                        ->booleanNode('default')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('proxy')
                            ->cannotBeEmpty()
                            ->defaultValue('pheanstalk.proxy.default')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
