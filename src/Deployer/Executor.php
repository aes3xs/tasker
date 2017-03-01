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

use Aes3xs\Yodler\Common\CallableHelper;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Scenario\ActionListInterface;
use Psr\Log\LoggerInterface;

/**
 * Executor implementation.
 */
class Executor implements ExecutorInterface
{
    /**
     * @var HeapInterface
     */
    protected $heap;

    /**
     * @var ReportInterface
     */
    protected $report;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param HeapInterface $heap
     * @param ReportInterface $report
     * @param LoggerInterface $logger
     */
    public function __construct(HeapInterface $heap, ReportInterface $report, LoggerInterface $logger)
    {
        $this->heap = $heap;
        $this->report = $report;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ActionListInterface $actions)
    {
        foreach ($actions->all() as $action) {
            try {
                $this->report->reportActionRunning($action);
                $this->logger->info('➤ ' . $action->getName());
                if ($action->getCondition() && !$this->heap->resolveExpression($action->getCondition())) {
                    $this->report->reportActionSkipped($action);
                    $this->logger->info('⇣ ' . $action->getName());
                    continue;
                }
                $output = $this->executeCallback($this->heap, $action->getCallback());
                $this->report->reportActionSucceed($action, $output);
                $this->logger->info('✔ ' . $action->getName());
                if ($output) {
                    $this->logger->info('• ' . $action->getName() . ': ' . (string) $output);
                }
            } catch (\Exception $e) {
                $this->report->reportActionError($action, $e);
                $this->logger->error('✘ ' . $action->getName(), [$e]);
                throw $e;
            }
        }
    }

    protected function executeCallback(HeapInterface $heap, callable $callback)
    {
        $arguments = [];
        foreach (CallableHelper::extractArguments($callback) as $argument) {
            $arguments[$argument] = $heap->get($argument);
        }
        return CallableHelper::call($callback, $arguments);
    }
}
