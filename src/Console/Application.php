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
use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Application.
 */
class Application extends BaseApplication implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->getDefinition()->addOption(new InputOption('file', null, InputOption::VALUE_OPTIONAL, 'Config file to load', 'config.yml'));
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (!$this->container) {
            throw new RuntimeException('Container is not properly set up');
        }

        return $this->container;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $event = new ConsoleRunEvent($this, $input, $output);

        // Dispatcher located in private property
        $this->getContainer()->get('event_dispatcher')->dispatch(ConsoleRunEvent::NAME, $event);

        return parent::doRun($input, $output);
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->getContainer());
        }

        return parent::doRunCommand($command, $input, $output);
    }
}
