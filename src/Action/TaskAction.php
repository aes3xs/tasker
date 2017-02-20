<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Action;

use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Common\CallableHelper;

/**
 * Task action is used to
 */
class TaskAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $taskName;

    /**
     * @var string
     */
    protected $condition;

    /**
     * Constructor.
     *
     * @param $taskName
     * @param $condition
     */
    public function __construct($taskName, $condition)
    {
        $this->taskName = $taskName;
        $this->condition = $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ucfirst($this->taskName);
    }

    /**
     * {@inheritdoc}
     */
    public function skip(HeapInterface $heap)
    {
        return $this->condition ? !boolval($heap->resolveExpression($this->condition)) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(HeapInterface $heap)
    {
        $callback = $heap->get($this->taskName);

        if (!is_callable($callback)) {
            throw new RuntimeException('Argument "task" must be callable, got: ' . var_export($callback, true));
        }

        $arguments = [];

        foreach (CallableHelper::extractArguments($callback) as $argument) {
            $arguments[$argument] = $heap->resolve($argument);
        }

        return CallableHelper::call($callback, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [$this->taskName];
    }
}
