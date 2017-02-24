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

use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Exception\RuntimeException;
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
     * @var DeployContextFactoryInterface
     */
    protected $deployContextFactory;

    /**
     * @var ExecutorInterface
     */
    protected $executor;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

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
     * @param DeployContextFactoryInterface $deployContextFactory
     * @param ExecutorInterface $executor
     * @param EventDispatcherInterface $eventDispatcher
     * @param SemaphoreInterface $semaphore
     * @param ReportInterface $report
     */
    public function __construct(
        DeployContextFactoryInterface $deployContextFactory,
        ExecutorInterface $executor,
        EventDispatcherInterface $eventDispatcher,
        SemaphoreInterface $semaphore,
        ReportInterface $report
    ) {
        $this->deployContextFactory = $deployContextFactory;
        $this->executor = $executor;
        $this->eventDispatcher = $eventDispatcher;
        $this->semaphore = $semaphore;
        $this->report = $report;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy(DeployInterface $deploy)
    {
        $this->semaphore->reset();
        $this->report->reset();

        $childPids = [];

        foreach ($deploy->getBuilds()->all() as $build) {

            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new RuntimeException('Could not fork');
            } else if ($pid) {
                $childPids[] = $pid; // Parent process code
                continue;
            }

            $deployContext = $this->deployContextFactory->create(
                $deploy,
                $build->getScenario(),
                $build->getConnection()
            );

            $event = new DeployEvent($deployContext);
            $this->eventDispatcher->dispatch(DeployEvent::NAME, $event);

            $this->report->initialize(getmypid());
            $this->report->reportDeploy($deployContext);
            $this->semaphore->reportReady(getmypid());

            try {
                $this->executor->execute($build->getScenario()->getActions());
            } catch (\Exception $e) {
                $this->semaphore->reportError();
                $this->executor->execute($build->getScenario()->getFailbackActions());
            }

            return;
        }

        $this->semaphore->run($childPids);

        foreach ($childPids as $pid) {
            pcntl_waitpid($pid, $status);
        }

        $deployContext = $this->deployContextFactory->create($deploy);

        $event = new DeployEvent($deployContext);
        $this->eventDispatcher->dispatch(DeployEvent::NAME, $event);

        $this->report->initialize(getmypid());
        $this->executor->execute($deploy->getDoneActions());
    }
}
