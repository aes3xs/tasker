<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Connection;

use Aes3xs\Yodler\Exception\FileReadException;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Connection factory implementation.
 */
class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var VariableFactoryInterface
     */
    protected $variableFactory;

    /**
     * Constructor.
     *
     * @param VariableFactoryInterface $variableFactory
     */
    public function __construct(VariableFactoryInterface $variableFactory)
    {
        $this->variableFactory = $variableFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createList()
    {
        return new ConnectionList();
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($connectionConfiguration)
    {
        $connections = new ConnectionList();

        $processor = new Processor();
        $connectionConfiguration = $processor->process($this->getConfigTreeBuilder()->buildTree(), [$connectionConfiguration]);

        foreach ($connectionConfiguration as $connectionName => $connectionData) {

            $server = new Server(
                isset($connectionData['host']) ? $connectionData['host'] : null,
                isset($connectionData['port']) ? $connectionData['port'] : null
            );

            $key = isset($connectionData['key']) && $connectionData['key'] ? $connectionData['key'] : null;
            if ($key && file_exists($key)) {
                $data = file_get_contents($key);
                if ($data === false) {
                    throw new FileReadException($key);
                }
                $key = $data;
            }

            $user = new User(
                isset($connectionData['login']) ? $connectionData['login'] : null,
                isset($connectionData['password']) ? $connectionData['password'] : null,
                $key,
                isset($connectionData['passphrase']) ? $connectionData['passphrase'] : null,
                isset($connectionData['forwarding']) ? $connectionData['forwarding'] : false
            );

            $variables = $this->variableFactory->createList($connectionData['variables']);

            $connection = new Connection($connectionName, $server, $user, $variables);

            $connections->add($connection);
        }

        return $connections;
    }

    /**
     * @return TreeBuilder
     */
    protected function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('connections');

        /** @var ArrayNodeDefinition $arrayNode */
        $arrayNode = $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array');

        $arrayNode
            ->children()
                ->scalarNode('host')->defaultValue(null)->end()
                ->integerNode('port')->defaultValue(null)->end()
                ->scalarNode('login')->defaultValue(null)->end()
                ->scalarNode('password')->defaultValue(null)->end()
                ->scalarNode('key')->defaultValue(null)->end()
                ->scalarNode('passphrase')->defaultValue(null)->end()
                ->booleanNode('forwarding')->defaultFalse()->end()
                ->arrayNode('variables')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
