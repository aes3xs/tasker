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
use Aes3xs\Yodler\Deployer\DeployerInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Scenario command exucutes configured scenario on selected connection.
 */
class ScenarioCommand extends Command
{
    /**
     * @var ScenarioInterface
     */
    protected $scenario;

    /**
     * @var ConnectionListInterface
     */
    protected $connections;

    /**
     * @var DeployerInterface
     */
    protected $deployer;

    /**
     * @var DeployFactoryInterface
     */
    protected $deployFactory;

    /**
     * Constructor.
     *
     * @param ScenarioInterface $scenario
     * @param ConnectionListInterface $connections
     * @param DeployerInterface $deployer
     * @param DeployFactoryInterface $deployFactory
     */
    public function __construct(
        ScenarioInterface $scenario,
        ConnectionListInterface $connections,
        DeployerInterface $deployer,
        DeployFactoryInterface $deployFactory
    ) {
        $this->scenario = $scenario;
        $this->connections = $connections;
        $this->deployer = $deployer;
        $this->deployFactory = $deployFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName($this->scenario->getName())
            ->addArgument('conn', InputArgument::REQUIRED, 'Define connection for scenario to run on');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->connections->get($input->getArgument('conn'));

        $deploy = $this->deployFactory->createFromScenarioAndConnection($this->scenario, $connection);

        $this->deployer->deploy($deploy);
    }
}
