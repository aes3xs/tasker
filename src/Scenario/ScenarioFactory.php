<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Scenario;

use Aes3xs\Yodler\Action\ActionFactoryInterface;
use Aes3xs\Yodler\Action\ActionList;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Scenario factory implementation.
 */
class ScenarioFactory implements ScenarioFactoryInterface
{
    /**
     * @var ActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * @var VariableFactoryInterface
     */
    protected $variableFactory;

    /**
     * Constructor.
     *
     * @param ActionFactoryInterface $actionFactory
     * @param VariableFactoryInterface $variableFactory
     */
    public function __construct(ActionFactoryInterface $actionFactory, VariableFactoryInterface $variableFactory)
    {
        $this->actionFactory = $actionFactory;
        $this->variableFactory = $variableFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($scenarioConfiguration)
    {
        $scenarios = new ScenarioList();

        $processor = new Processor();
        $scenarioConfiguration = $processor->process($this->getConfigTreeBuilder()->buildTree(), [$scenarioConfiguration]);

        foreach ($scenarioConfiguration as $scenarioName => $scenarioData) {
            $actions = $this->actionFactory->createListFromConfiguration($scenarioData['actions']);
            $failbackActions = $this->actionFactory->createListFromConfiguration($scenarioData['failback']);
            $variables = $this->variableFactory->createList($scenarioData['variables']);

            $scenario = new Scenario($scenarioName, $actions, $failbackActions, $variables);
            $scenarios->add($scenario);
        }

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function createStubScenario()
    {
        return new Scenario(null, $this->actionFactory->createList(), $this->actionFactory->createList(), $this->variableFactory->createList());
    }

    /**
     * @return TreeBuilder
     */
    protected function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scenarios');

        /** @var ArrayNodeDefinition $arrayNode */
        $arrayNode = $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array');

        $arrayNode
            ->children()
                ->arrayNode('actions')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('failback')
                    ->defaultValue([])
                    ->prototype('variable')->end()
                ->end()
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
