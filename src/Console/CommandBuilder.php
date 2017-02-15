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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds console commands using configuration.
 */
class CommandBuilder implements EventSubscriberInterface
{
    protected $input;
    protected $output;

    /**
     * Returns array of console commands.
     *
     * @return Command[]
     */
    protected function build()
    {
        $commands = [];

        $commands[] = new Command('test');

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
}
