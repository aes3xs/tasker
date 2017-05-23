<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests;

use Aes3xs\Yodler\Console\Application;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Kernel;
use Monolog\Logger;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\ClassLoader\MapClassLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractFunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var BufferedOutput
     */
    protected $output;

    protected function getContainer()
    {
        if (!$this->kernel) {
            $this->kernel = new Kernel(__DIR__ . '/Fixtures/container/default.yml');
            $this->kernel->boot();

            foreach ($this->kernel->getContainer()->getParameter('autoload') as $dir) {
                $map = ClassMapGenerator::createMap($dir);
                $map_loader = new MapClassLoader($map);
                $map_loader->register();
            }

            $application = new Application();
            $this->input = new ArrayInput([]);
            $this->output = new BufferedOutput();
            $this->output->setVerbosity(Logger::DEBUG);

            $event = new ConsoleRunEvent($application, $this->input, $this->output);
            $this->getContainer()->get('event_dispatcher')->dispatch(ConsoleRunEvent::NAME, $event);

            $event = new ConsoleCommandEvent(new Command('test'), $this->input, $this->output);
            $this->getContainer()->get('event_dispatcher')->dispatch(ConsoleEvents::COMMAND, $event);
        }

        return $this->kernel->getContainer();
    }

    protected function getOutput()
    {
        return $this->output->fetch();
    }
}
