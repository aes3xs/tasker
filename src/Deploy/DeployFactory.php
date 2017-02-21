<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deploy;

use Aes3xs\Yodler\Action\ActionFactoryInterface;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ConnectionListInterface;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioListInterface;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Deploy factory implementation.
 */
class DeployFactory implements DeployFactoryInterface
{
    /**
     * @var ScenarioListInterface
     */
    protected $scenarios;

    /**
     * @var ConnectionListInterface
     */
    protected $connections;

    /**
     * @var VariableFactoryInterface
     */
    protected $variableFactory;

    /**
     * @var ActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * Constructor.
     *
     * @param ScenarioListInterface $scenarios
     * @param ConnectionListInterface $connections
     * @param VariableFactoryInterface $variableFactory
     * @param ActionFactoryInterface $actionFactory
     */
    public function __construct(
        ScenarioListInterface $scenarios,
        ConnectionListInterface $connections,
        VariableFactoryInterface $variableFactory,
        ActionFactoryInterface $actionFactory
    ) {
        $this->scenarios = $scenarios;
        $this->connections = $connections;
        $this->variableFactory = $variableFactory;
        $this->actionFactory = $actionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($deployConfiguration)
    {
        $deploys = new DeployList();

        $processor = new Processor();
        $deployConfiguration = $processor->process($this->getConfigTreeBuilder()->buildTree(), [$deployConfiguration]);

        foreach ($deployConfiguration as $deployName => $deployData) {

            $builds = new BuildList();

            foreach ($deployData['builds'] as $buildData) {

                if (!isset($buildData['scenario'])) {
                    throw new RuntimeException('Scenario name must be provided, got: ' . var_export($buildData, true));
                }

                if (!isset($buildData['connection'])) {
                    throw new RuntimeException('Scenario name must be provided, got: ' . var_export($buildData, true));
                }

                $scenario = $this->scenarios->get($buildData['scenario']);
                $connection = $this->connections->get($buildData['connection']);
                $build = new Build($scenario, $connection);
                $builds->add($build);
            }

            $doneActions = $this->actionFactory->createListFromConfiguration($deployData['done']);
            $variables = $this->variableFactory->createList(isset($deployData['variables']) ? $deployData['variables'] : []);
            $deploy = new Deploy($deployName, $builds, $doneActions, $variables);
            $deploys->add($deploy);
        }

        return $deploys;
    }

    /**
     * {@inheritdoc}
     */
    public function createStubDeploy()
    {
        return new Deploy(
            null,
            new BuildList(),
            $this->actionFactory->createList(),
            $this->variableFactory->createList()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createFromScenarioAndConnection(ScenarioInterface $scenario, ConnectionInterface $connection)
    {
        $builds = new BuildList();
        $build = new Build($scenario, $connection);
        $builds->add($build);

        return new Deploy(
            null,
            $builds,
            $this->actionFactory->createList(),
            $this->variableFactory->createList()
        );
    }

    /**
     * @return TreeBuilder
     */
    protected function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deploys');

        /** @var ArrayNodeDefinition $arrayNode */
        $arrayNode = $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array');

        $arrayNode
            ->children()
                ->arrayNode('builds')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('scenario')->isRequired()->end()
                            ->scalarNode('connection')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('variables')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('done')
                    ->defaultValue([])
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
