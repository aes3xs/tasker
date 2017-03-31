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

use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioListInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds console commands from configured scenarios and deploys.
 */
class CommandBuilder implements EventSubscriberInterface
{
    /**
     * @var ScenarioListInterface
     */
    protected $scenarios;

    /**
     * Constructor.
     *
     * @param ScenarioListInterface $scenarios
     */
    public function __construct(ScenarioListInterface $scenarios)
    {
        $this->scenarios = $scenarios;
    }

    /**
     * @param ConsoleRunEvent $event
     */
    public function onConsoleRun(ConsoleRunEvent $event)
    {
        $event->getApplication()->addCommands($this->build());
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
     * @return Command[]
     */
    public function build()
    {
        $commands = [];
        foreach ($this->scenarios->all() as $scenario) {
            $commands[] = $this->buildScenarioCommand($scenario);
        }
        return $commands;
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @return ScenarioCommand
     */
    protected function buildScenarioCommand(ScenarioInterface $scenario)
    {
        $command = new ScenarioCommand($scenario);

        foreach ($scenario->getVariables()->all() as $name => $value) {
            if ($value instanceof InputArgument && !$command->getDefinition()->hasArgument($value->getName())) {
                $command->getDefinition()->addArgument($value);
            }
            if ($value instanceof InputOption && !$command->getDefinition()->hasOption($value->getName())) {
                $command->getDefinition()->addOption($value);
            }
        }

        return $command;
    }
}
