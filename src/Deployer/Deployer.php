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

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Heap\HeapFactoryInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Scenario\Action;
use Aes3xs\Yodler\Scenario\Scenario;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Deployer implementation.
 */
class Deployer implements DeployerInterface
{
    /**
     * @var HeapFactoryInterface
     */
    protected $heapFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param HeapFactoryInterface $heapFactory
     * @param Logger $logger
     */
    public function __construct(HeapFactoryInterface $heapFactory, Logger $logger)
    {
        $this->heapFactory = $heapFactory;
        $this->logger = $logger;
    }

    /**
     * Run deploy process.
     *
     * @param Scenario $scenario
     * @param Connection $connection
     * @param SemaphoreInterface $semaphore
     * @param ReporterInterface $reporter
     */
    public function deploy(
        Scenario $scenario,
        Connection $connection,
        SemaphoreInterface $semaphore,
        ReporterInterface $reporter
    ) {
        $logger = $this->logger->withName(sprintf('%s@%s', $scenario->getName(), $connection->getName()));
        $heap = $this->heapFactory->create($scenario, $connection);

        $semaphore->reportReady();
        $reporter->reportDeploy($scenario, $connection);

        try {
            $this->execute($scenario->getActions(), $heap, $reporter, $logger);
        } catch (\Exception $e) {
            $semaphore->reportError();
            $heap->set('exception', $e);
            $this->execute($scenario->getFailbacks(), $heap, $reporter, $logger);
        }
    }

    /**
     * Execute actions.
     *
     * @param array $actions
     * @param HeapInterface $heap
     * @param ReporterInterface $reporter
     * @param LoggerInterface $logger
     * @throws \Exception
     */
    protected function execute(
        array $actions,
        HeapInterface $heap,
        ReporterInterface $reporter,
        LoggerInterface $logger
    ) {
        foreach ($actions as $action) {
            /** @var Action $action */
            try {
                $reporter->reportActionRunning($action);
                $logger->info('➤ ' . $action->getName());
                if ($action->getCondition() && !$heap->resolveExpression($action->getCondition())) {
                    $reporter->reportActionSkipped($action);
                    $logger->info('⇣ ' . $action->getName());
                    continue;
                }
                $output = $heap->resolveCallback($action->getCallback());
                $reporter->reportActionSucceed($action, $output);
                $logger->info('✔ ' . $action->getName());
                if ($output) {
                    $logger->info('• ' . $action->getName() . ': ' . (string) $output);
                }
            } catch (\Exception $e) {
                $reporter->reportActionError($action, $e);
                $logger->error('✘ ' . $action->getName(), ['exception' => $e]);
                throw $e;
            }
        }
    }
}
