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
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Heap\HeapFactoryInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioListInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @var HeapFactoryInterface
     */
    protected $heapFactory;

    /**
     * @var DeployContextFactoryInterface
     */
    protected $deployContextFactory;

    /**
     * Constructor.
     *
     * @param DeployListInterface $deploys
     * @param ScenarioListInterface $scenarios
     * @param HeapFactoryInterface $heapFactory
     * @param DeployContextFactoryInterface $deployContextFactory
     */
    public function __construct(
        DeployListInterface $deploys,
        ScenarioListInterface $scenarios,
        HeapFactoryInterface $heapFactory,
        DeployContextFactoryInterface $deployContextFactory
    ) {
        $this->deploys = $deploys;
        $this->scenarios = $scenarios;
        $this->heapFactory = $heapFactory;
        $this->deployContextFactory = $deployContextFactory;
    }

    /**
     * @param ConsoleRunEvent $event
     */
    public function onConsoleRun(ConsoleRunEvent $event)
    {
        $event->getApplication()->addCommands($this->build($event->getInput(), $event->getOutput()));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleRunEvent::NAME => ['onConsoleRun', 255],
        ];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return Command[]
     */
    public function build(InputInterface $input, OutputInterface $output)
    {
        $commands = [];
        foreach ($this->deploys->all() as $deploy) {
            $commands[] = $this->buildDeployCommand($deploy, $input, $output);
        }
        foreach ($this->scenarios->all() as $scenario) {
            $commands[] = $this->buildScenarioCommand($scenario, $input, $output);
        }
        return $commands;
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return DeployCommand
     */
    protected function buildDeployCommand(DeployInterface $deploy, InputInterface $input, OutputInterface $output)
    {
        $command = new DeployCommand($deploy);

        foreach ($deploy->getBuilds()->all() as $build) {
            $deployContext = $this->deployContextFactory
                ->create(
                    $deploy,
                    $build->getScenario(),
                    $build->getConnection()
                );
            $heap = $this->heapFactory->create($deployContext, $input, $output);
            $this->collectScenarioInputDefinitions($build->getScenario(), $command->getDefinition(), $heap);
        }

        return $command;
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return ScenarioCommand
     */
    protected function buildScenarioCommand(ScenarioInterface $scenario, InputInterface $input, OutputInterface $output)
    {
        $command = new ScenarioCommand($scenario);

        $deployContext = $this->deployContextFactory->create(
            null,
            $scenario
        );
        $heap = $this->heapFactory->create($deployContext, $input, $output);
        $this->collectScenarioInputDefinitions($scenario, $command->getDefinition(), $heap);

        return $command;
    }
}
