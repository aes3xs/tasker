<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DeploysConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deploys');

        /** @var ArrayNodeDefinition $arrayNode */
        $arrayNode = $rootNode
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array');

        $arrayNode
            ->children()

                ->scalarNode('scenario')->isRequired()->cannotBeEmpty()->end()

                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')->defaultValue(null)->end()
                        ->integerNode('port')->defaultValue(null)->end()
                        ->scalarNode('login')->defaultValue(null)->end()
                        ->scalarNode('password')->defaultValue(null)->end()
                        ->scalarNode('public_key')->defaultValue(null)->end()
                        ->scalarNode('private_key')->defaultValue(null)->end()
                        ->scalarNode('passphrase')->defaultValue(null)->end()
                        ->booleanNode('forwarding')->defaultFalse()->end()
                    ->end()
                ->end()

                ->arrayNode('parameters')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
