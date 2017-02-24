<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Console;

use Aes3xs\Yodler\Connection\ConnectionListInterface;
use Aes3xs\Yodler\Deploy\DeployFactoryInterface;
use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Deploy\DeployListInterface;
use Aes3xs\Yodler\Deployer\DeployContextFactoryInterface;
use Aes3xs\Yodler\Deployer\DeployerInterface;
use Aes3xs\Yodler\Heap\HeapFactoryInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioListInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds console commands from configured scenarios and deploys.
 */
class CommandBuilder implements EventSubscriberInterface
{
    /**
     * @var DeployListInterface
     */
    protected $deploys;

    /**
     * @var ScenarioListInterface
     */
    protected $scenarios;

    /**
     * @var ConnectionListInterface
     */
    protected $connections;

    /**
     * @var HeapFactoryInterface
     */
    protected $heapFactory;

    /**
     * @var DeployContextFactoryInterface
     */
    protected $deployContextFactory;

    /**
     * @var DeployFactoryInterface
     */
    protected $deployFactory;

    /**
     * @var DeployerInterface
     */
    protected $deployer;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        DeployListInterface $deploys,
        ScenarioListInterface $scenarios,
        ConnectionListInterface $connections,
        HeapFactoryInterface $heapFactory,
        DeployContextFactoryInterface $deployContextFactory,
        DeployFactoryInterface $deployFactory,
        DeployerInterface $deployer
    ) {
        $this->deploys = $deploys;
        $this->scenarios = $scenarios;
        $this->connections = $connections;
        $this->heapFactory = $heapFactory;
        $this->deployContextFactory = $deployContextFactory;
        $this->deployFactory = $deployFactory;
        $this->deployer = $deployer;
    }

    /**
     * @return array
     */
    public function build()
    {
        $commands = [];

        foreach ($this->deploys->all() as $deploy) {
            $commands[] = $this->buildDeployCommand($deploy);
        }

        foreach ($this->scenarios->all() as $scenario) {
            $commands[] = $this->buildScenarioCommand($scenario);
        }

        return $commands;
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $this->input = $event->getInput();
        $this->output = $event->getOutput();

        $event->getCommand()->getApplication()->addCommands($this->build());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 255],
        ];
    }

    /**
     * @param ScenarioInterface $scenario
     * @param InputDefinition $inputDefinition
     * @param HeapInterface $heap
     */
    protected function collectScenarioInputDefinitions(
        ScenarioInterface $scenario,
        InputDefinition $inputDefinition,
        HeapInterface $heap
    ) {
        foreach ($scenario->getActions()->all() as $action) {
            $dependencies = [];
            foreach ($action->getDependencies() as $dependency) {
                $dependencies = array_merge($dependencies, $heap->getDependencies($dependency));
            }
            $dependencies = array_unique($dependencies);
            foreach ($dependencies as $dependency) {
                $value = $heap->get($dependency);
                if ($value instanceof InputArgument && !$inputDefinition->hasArgument($value->getName())) {
                    $inputDefinition->addArgument($value);
                }
                if ($value instanceof InputOption && !$inputDefinition->hasOption($value->getName())) {
                    $inputDefinition->addOption($value);
                }
            }
        }
    }

    /**
     * @param DeployInterface $deploy
     *
     * @return DeployCommand
     */
    protected function buildDeployCommand(DeployInterface $deploy)
    {
        $command = new DeployCommand($deploy, $this->deployer);

        foreach ($deploy->getBuilds()->all() as $build) {
            $deployContext = $this->deployContextFactory
                ->create(
                    $deploy,
                    $build->getScenario(),
                    $build->getConnection()
                );
            $heap = $this->heapFactory->create($deployContext, $this->input, $this->output);
            $this->collectScenarioInputDefinitions($build->getScenario(), $command->getDefinition(), $heap);
        }

        return $command;
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @return ScenarioCommand
     */
    protected function buildScenarioCommand(ScenarioInterface $scenario)
    {
        $command = new ScenarioCommand($scenario, $this->connections, $this->deployer, $this->deployFactory);

        $deployContext = $this->deployContextFactory->create(
            null,
            $scenario
        );
        $heap = $this->heapFactory->create($deployContext, $this->input, $this->output);
        $this->collectScenarioInputDefinitions($scenario, $command->getDefinition(), $heap);

        return $command;
    }
}
