<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LazyHeapProxy implements HeapInterface, EventSubscriberInterface
{
    /**
     * @var HeapInterface
     */
    protected $heap;

    /**
     * @var HeapFactoryInterface
     */
    protected $heapFactory;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ScenarioInterface
     */
    protected $scenario;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param HeapFactoryInterface $heapFactory
     */
    public function __construct(HeapFactoryInterface $heapFactory)
    {
        $this->heapFactory = $heapFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->getHeap()->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->getHeap()->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveString($string)
    {
        return $this->getHeap()->resolveString($string);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveExpression($expression)
    {
        return $this->getHeap()->resolveExpression($expression);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCallback(callable $callback)
    {
        return $this->getHeap()->resolveCallback($callback);
    }

    /**
     * @param ConsoleRunEvent $event
     */
    public function onConsoleRun(ConsoleRunEvent $event)
    {
        $this->input = $event->getInput();
        $this->output = $event->getOutput();
    }

    /**
     * @param DeployEvent $event
     */
    public function onDeploy(DeployEvent $event)
    {
        $this->scenario = $event->getScenario();
        $this->connection = $event->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleRunEvent::NAME => ['onConsoleRun', 255],
            DeployEvent::NAME     => ['onDeploy', 255],
        ];
    }

    /**
     * @return HeapInterface
     */
    protected function getHeap()
    {
        if (!$this->heap) {
            if (!$this->scenario || !$this->connection) {
                throw new RuntimeException('Deploy event was never invoked');
            }
            if (!$this->input || !$this->output) {
                throw new RuntimeException('Command event was never invoked');
            }
            $this->heap = $this->heapFactory->create($this->scenario, $this->connection, $this->input, $this->output);
        }

        return $this->heap;
    }
}
