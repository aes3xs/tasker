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
use Aes3xs\Yodler\Scenario\Scenario;
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
     * @var Scenario
     */
    protected $scenario;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param Scenario $scenario
     */
    public function __construct(Scenario $scenario)
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
        $defaultConnection = $this->scenario->getVariables() && $this->scenario->getVariables()->has('connection')
            ? $this->scenario->getVariables()->get('connection')
            : null;

        $this
            ->setName($this->scenario->getName())
            ->addArgument('connection', InputArgument::OPTIONAL, 'Define connection for scenario to run on', $defaultConnection);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('connection_manager')->get($input->getArgument('connection'));

        $key = $this->scenario->getName() . $connection->getName();

        $semaphore = $this->getContainer()->get('semaphore_factory')->create($key);
        $reporter = $this->getContainer()->get('reporter_factory')->create($key);

        $semaphore->reset();
        $reporter->reset();

        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new RuntimeException('Could not fork');
        } else if ($pid) {
            // Parent process code
            $semaphore->addProcess($pid);
            $pids[] = $pid;
            $semaphore->run();
            pcntl_waitpid($pid, $status);
        } else {
            // Child process code
            $this->getContainer()->get('deployer')->deploy($this->scenario, $connection, $semaphore, $reporter);
            return;
        }

        ReportPrinter::printReport($reporter, $input, $output);
    }
}
