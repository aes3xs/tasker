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

use Aes3xs\Yodler\Deployer\SemaphoreInterface;
use Aes3xs\Yodler\Heap\HeapInterface;

/**
 * Checkpoint action is used to work together with Semaphore.
 *
 * Checkpoints are used to synchronize parallel builds.
 */
class CheckpointAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $checkpoint;

    /**
     * @var SemaphoreInterface
     */
    protected $semaphore;

    /**
     * Constructor.
     *
     * @param $checkpoint
     * @param SemaphoreInterface $semaphore
     */
    public function __construct($checkpoint, SemaphoreInterface $semaphore)
    {
        $this->checkpoint = $checkpoint;
        $this->semaphore = $semaphore;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->checkpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(HeapInterface $heap)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(HeapInterface $heap)
    {
        $this->semaphore->reportCheckpoint($this->checkpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [];
    }
}
