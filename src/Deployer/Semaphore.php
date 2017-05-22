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

use Aes3xs\Yodler\Common\LockableStorage;
use Aes3xs\Yodler\Exception\ErrorInterruptException;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Exception\TimeoutInterruptException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Semaphore implementation.
 *
 * Coordinates and synchronizes process execution.
 */
class Semaphore implements SemaphoreInterface
{
    const STATE_SUSPENDED = 'Suspended';
    const STATE_RUNNING = 'Running';

    const SLEEP_MS = 100;
    const TIMEOUT = 300;

    const CHECKPOINT_INIT = '@init';
    const CHECKPOINT_ERROR = '@error';

    /**
     * @var LockableStorage
     */
    protected $storage;

    /**
     * @var int
     */
    protected $timeout = self::TIMEOUT;

    /**
     * Constructor.
     *
     * @param LockableStorage $storage
     */
    public function __construct(LockableStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->lock();
        $this->setData([
            'state'       => self::STATE_SUSPENDED,
            'checkpoints' => [],
        ]);
        $this->release();
    }

    /**
     * {@inheritdoc}
     */
    public function addProcess($pid)
    {
        $this->lock();
        $data = $this->getData();
        if (!array_key_exists($pid, $data['checkpoints'])) {
            $data['checkpoints'][$pid] = [];
        }
        $this->setData($data);
        $this->release();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $timer = 0;
        while (true) {
            $this->lock();
            $data = $this->getData();
            $this->release();
            foreach ($data['checkpoints'] as $checkpoints) {
                if (!in_array(self::CHECKPOINT_INIT, $checkpoints)) {
                    $this->sleep($timer);
                    break;
                }
            }
            break;
        }

        $this->lock();
        $data = $this->getData();
        $data['state'] = self::STATE_RUNNING;
        $this->setData($data);
        $this->release();
    }

    /**
     * {@inheritdoc}
     */
    public function reportReady()
    {
        $timer = 0;
        while (true) {

            $this->lock();
            $data = $this->getData();

            if (!isset($data['checkpoints'][getmypid()])) {
                $this->release();
                $this->sleep($timer);
                continue;
            }
            if (!in_array(self::CHECKPOINT_INIT, $data['checkpoints'][getmypid()])) {
                $data['checkpoints'][getmypid()][] = self::CHECKPOINT_INIT;
                $this->setData($data);
            }

            $this->release();

            if ($data['state'] === self::STATE_RUNNING) {
                break;
            }
            $this->sleep($timer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reportCheckpoint($name)
    {
        $this->lock();
        $data = $this->getData();
        if (!isset($data['checkpoints'][getmypid()])) {
            throw new RuntimeException('No checkpoint data found for ID: ' . getmypid());
        }
        $data['checkpoints'][getmypid()][] = $name;
        $this->setData($data);
        $this->release();

        if (self::CHECKPOINT_ERROR === $name) {
            return;
        }

        $timer = 0;
        while (true) {

            $this->lock();
            $data = $this->getData();
            if (!isset($data['checkpoints'][getmypid()])) {
                throw new RuntimeException('No checkpoint data found for ID: ' . getmypid());
            }
            $currentCheckpoints = $data['checkpoints'][getmypid()];
            $this->release();

            $wait = false;
            $error = false;

            foreach ($data['checkpoints'] as $checkpoints) {
                $missCheckpoints = !empty(array_diff($currentCheckpoints, $checkpoints));
                $hasError = in_array(self::CHECKPOINT_ERROR, $checkpoints);
                $wait = $wait || $missCheckpoints;
                $error = $error || $hasError;
            }

            if ($error) {
                throw new ErrorInterruptException();
            }
            if (!$wait) {
                break;
            }

            $this->sleep($timer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reportError()
    {
        $this->reportCheckpoint(self::CHECKPOINT_ERROR);
    }

    /**
     * Set wait timeout.
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Sleep.
     *
     * Throws an error when overall time reaches timeout value.
     *
     * @param $timer
     *
     * @throws TimeoutInterruptException
     */
    protected function sleep(&$timer)
    {
        usleep(self::SLEEP_MS * 1000);
        $timer += self::SLEEP_MS / 1000;
        if ($timer >= $this->timeout) {
            throw new TimeoutInterruptException();
        }
    }

    /**
     * Retrieve and validate data from storage.
     *
     * @return array
     */
    protected function getData()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('semaphore');

        $rootNode
            ->children()
            ->scalarNode('state')
                ->isRequired()
                ->validate()
                    ->ifNotInArray([self::STATE_SUSPENDED, self::STATE_RUNNING])
                    ->thenInvalid('State is invalid')
                ->end()
            ->end()
            ->arrayNode('checkpoints')
                ->isRequired()
                ->prototype('array')
                    ->prototype('scalar')->isRequired()->end()
                ->end()
            ->end()
        ;

        $data = $this->storage->read();

        $processor = new Processor();
        $data = $processor->process($treeBuilder->buildTree(), [$data]);

        return $data;
    }

    /**
     * Set data to storage.
     *
     * @param $data
     */
    protected function setData($data)
    {
        $this->storage->write($data);
    }

    /**
     * Lock storage with blocking.
     */
    protected function lock()
    {
        $this->storage->acquire(true);
    }

    /**
     * Release storage lock.
     */
    protected function release()
    {
        $this->storage->release();
    }

    /**
     * Get current process checkpoints.
     *
     * @return array
     */
    protected function getCurrentProcessCheckpoints()
    {
        $data = $this->getData();

        if (!isset($data['checkpoints'][getmypid()])) {
            throw new RuntimeException('No checkpoint data found for ID: ' . getmypid());
        }

        return $data;
    }

    /**
     * Add checkpoint to current process.
     *
     * @param $checkpoint
     */
    protected function addCurrentProcessCheckpoint($checkpoint)
    {
        $data = $this->getData();

        if (!isset($data['checkpoints'][getmypid()])) {
            throw new RuntimeException('No checkpoint data found for ID: ' . getmypid());
        }

        $data['checkpoints'][getmypid()][] = $checkpoint;

        $this->setData($data);
    }
}
