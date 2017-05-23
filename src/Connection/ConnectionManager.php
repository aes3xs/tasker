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

use Aes3xs\Yodler\Exception\ConnectionNotFoundException;
use Aes3xs\Yodler\Exception\FileReadException;
use Aes3xs\Yodler\Variable\VariableList;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Connection manager implementation.
 */
class ConnectionManager
{
    /**
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * Constructor.
     *
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $this->connections = $this->createListFromConfiguration($configuration);
    }

    /**
     * Get scenario from a list by name.
     *
     * @param $name
     *
     * @return Connection
     *
     * @throws ConnectionNotFoundException
     */
    public function get($name)
    {
        if (!isset($this->connections[$name])) {
            throw new ConnectionNotFoundException($name);
        }

        return $this->connections[$name];
    }

    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $configuration
     *
     * @return Connection[]
     *
     * @throws FileReadException
     */
    protected function createListFromConfiguration($configuration)
    {
        $connections = [];

        $processor = new Processor();
        $configuration = $processor->process($this->getConfigTreeBuilder()->buildTree(), [$configuration]);

        foreach ($configuration as $name => $data) {

            $connection = new Connection($name);
            $connections[$name] = $connection;

            $connection
                ->setHost($data['host'])
                ->setPort($data['port'])
                ->setLogin($data['login'])
                ->setPassword($data['password'])
                ->setPassphrase($data['passphrase'])
                ->setForwarding($data['forwarding']);

            $key = $data['key'];
            if ($key && file_exists($key)) {
                $keyContent = file_get_contents($key);
                if ($keyContent === false) {
                    throw new FileReadException($key);
                }
                $key = $keyContent;
            }

            $connection->setKey($key);

            if ($data['variables']) {
                $connection->setVariables(new VariableList($data['variables']));
            }
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
