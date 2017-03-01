<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deployer;

use Aes3xs\Yodler\Connection\ConnectionFactoryInterface;
use Aes3xs\Yodler\Connection\ConnectionListInterface;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Deployer implementation.
 */
class Deployer implements DeployerInterface
{
    const STATE_NONE = 'None';
    const STATE_SKIP = 'Skip';
    const STATE_EXECUTE = 'Execute';
    const STATE_SUCCESS = 'Success';
    const STATE_ERROR = 'Error';
    const STATE_INTERRUPT = 'Interrupted';

    /**
     * @var ExecutorInterface
     */
    protected $executor;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * @var SemaphoreInterface
     */
    protected $semaphore;

    /**
     * @var ReportInterface
     */
    protected $report;

    /**
     * Constructor.
     *
     * @param ExecutorInterface $executor
     * @param EventDispatcherInterface $eventDispatcher
     * @param ConnectionFactoryInterface $connectionFactory
     * @param SemaphoreInterface $semaphore
     * @param ReportInterface $report
     */
    public function __construct(
        ExecutorInterface $executor,
        EventDispatcherInterface $eventDispatcher,
        ConnectionFactoryInterface $connectionFactory,
        SemaphoreInterface $semaphore,
        ReportInterface $report
    ) {
        $this->executor = $executor;
        $this->eventDispatcher = $eventDispatcher;
        $this->connectionFactory = $connectionFactory;
        $this->semaphore = $semaphore;
        $this->report = $report;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy(ScenarioInterface $scenario, ConnectionListInterface $connections)
    {
        $this->semaphore->reset();
        $this->report->reset();

        $childPids = [];

        foreach ($connections->all() as $connection) {

            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new RuntimeException('Could not fork');
            } else if ($pid) {
                $childPids[] = $pid; // Parent process code
                continue;
            }

            $event = new DeployEvent($scenario, $connection);
            $this->eventDispatcher->dispatch(DeployEvent::NAME, $event);

            $this->report->initialize(getmypid());
            $this->report->reportDeploy($scenario, $connection);
            $this->semaphore->reportReady(getmypid());

            try {
                $this->executor->execute($scenario->getActions());
            } catch (\Exception $e) {
                $this->semaphore->reportError();
                $this->executor->execute($scenario->getFailbackActions());
            }

            return false;
        }

        $this->semaphore->run($childPids);

        foreach ($childPids as $pid) {
            pcntl_waitpid($pid, $status);
        }

        return true;
    }
}
