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
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\Scenario\ScenarioManager;
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
     * @var ScenarioManager
     */
    protected $scenarioManager;

    /**
     * Constructor.
     *
     * @param ScenarioManager $scenarioManager
     */
    public function __construct(ScenarioManager $scenarioManager)
    {
        $this->scenarioManager = $scenarioManager;
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
        foreach ($this->scenarioManager->all() as $scenario) {
            $commands[] = $this->buildScenarioCommand($scenario);
        }
        return $commands;
    }

    /**
     * @param Scenario $scenario
     *
     * @return ScenarioCommand
     */
    protected function buildScenarioCommand(Scenario $scenario)
    {
        $command = new ScenarioCommand($scenario);

        $arguments = $command->getDefinition()->getArguments();
        $options = $command->getDefinition()->getOptions();

        foreach ($scenario->getVariables()->all() as $name => $value) {
            if ($value instanceof InputArgument) {
                $arguments[$value->getName()] = $value;
            }
            if ($value instanceof InputOption) {
                $options[$value->getName()] = $value;
            }
        }

        $command->getDefinition()->setArguments($arguments);
        $command->getDefinition()->setOptions($options);

        return $command;
    }
}
