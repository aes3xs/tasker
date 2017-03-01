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

use Aes3xs\Yodler\Common\ReportPrinter;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Scenario command exucutes configured scenario on selected connection.
 */
class ScenarioCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ScenarioInterface
     */
    protected $scenario;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ScenarioInterface $scenario
     */
    public function __construct(ScenarioInterface $scenario)
    {
        $this->scenario = $scenario;

        parent::__construct();
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

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName($this->scenario->getName())
            ->addArgument('conn', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Define connections for scenario to run on');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connections = $this->getContainer()->get('connection_factory')->createList();
        foreach ($input->getArgument('conn') as $connectionName) {
            $connection = $this->getContainer()->get('connections')->get($connectionName);
            $connections->add($connection);
        }

        if ($this->getContainer()->get('deployer')->deploy($this->scenario, $connections)) {
            ReportPrinter::printResult($this->getContainer()->get('report')->getRawData(), $input, $output);
        }
    }
}
