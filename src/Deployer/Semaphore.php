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

use Aes3xs\Yodler\Exception\ErrorInterruptException;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Common\SharedMemoryHandler;
use Aes3xs\Yodler\Exception\TimeoutInterruptException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\LockHandler;

class Semaphore implements SemaphoreInterface
{
    const STATE_SUSPENDED = 'Suspended';
    const STATE_RUNNING = 'Running';

    const SLEEP_MS = 100;
    const TIMEOUT = 300;
    const CHECKPOINT_ERROR = '@error';

    /**
     * @var LockHandler
     */
    protected $lockHandler;

    /**
     * @var SharedMemoryHandler
     */
    protected $sharedMemoryHandler;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $timeout = self::TIMEOUT;

    /**
     * Constructor.
     *
     * @param LockHandler $lockHandler
     * @param SharedMemoryHandler $sharedMemoryHandler
     */
    public function __construct(LockHandler $lockHandler, SharedMemoryHandler $sharedMemoryHandler)
    {
        $this->lockHandler = $lockHandler;
        $this->sharedMemoryHandler = $sharedMemoryHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->lockHandler->lock(true);
        $this->sharedMemoryHandler->dump([
            'state'          => self::STATE_SUSPENDED,
            'concurrent_ids' => [],
            'checkpoints'    => [],
        ]);
        $this->lockHandler->release();
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $concurrentIds)
    {
        $timer = 0;
        while (true) {
            $this->lockHandler->lock(true);
            $data = $this->getData(false);
            $this->lockHandler->release();
            if (!array_diff($concurrentIds, array_keys($data['checkpoints']))) {
                break;
            }
            $this->sleep($timer);
        }

        $this->lockHandler->lock(true);
        $data = $this->getData(false);
        $data['state'] = self::STATE_RUNNING;
        $data['concurrent_ids'] = $concurrentIds;
        $this->sharedMemoryHandler->dump($data);
        $this->lockHandler->release();
    }

    /**
     * {@inheritdoc}
     */
    public function reportReady($id)
    {
        if (!is_scalar($id) || !$id) {
            throw new RuntimeException('ID must be scalar and non-empty');
        }

        $this->id = $id;

        $timer = 0;
        while (true) {
            $this->lockHandler->lock(true);
            $data = $this->getData(false);
            $data['checkpoints'][$this->getId()] = [];
            $this->sharedMemoryHandler->dump($data);
            $this->lockHandler->release();
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
        $this->lockHandler->lock(true);
        $data = $this->getData();
        $data['checkpoints'][$this->getId()][] = $name;
        $this->sharedMemoryHandler->dump($data);
        $this->lockHandler->release();

        $timer = 0;
        while (true) {

            $this->lockHandler->lock(true);
            $data = $this->getData();
            $wait = false;
            $error = false;
            foreach ($data['concurrent_ids'] as $id) {
                $missCheckpoints = !empty(array_diff($data['checkpoints'][$this->getId()], $data['checkpoints'][$id]));
                $hasError = in_array(self::CHECKPOINT_ERROR, $data['checkpoints'][$id]);
                $wait = $wait || $missCheckpoints;
                $error = $error || $hasError;
            }
            $this->lockHandler->release();

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
        $this->lockHandler->lock(true);
        $data = $this->getData();
        $data['checkpoints'][$this->getId()][] = self::CHECKPOINT_ERROR;
        $this->sharedMemoryHandler->dump($data);
        $this->lockHandler->release();
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
     * Return process configured ID.
     *
     * @return int
     */
    protected function getId()
    {
        if (!$this->id) {
            throw new RuntimeException('Semaphore is not properly initialized. Process must call reportReady() first.');
        }

        return $this->id;
    }

    /**
     * Retrieve and validate data from shared memory.
     *
     * By default checks configured ID to be presented.
     *
     * @param bool $checkId
     *
     * @return array
     */
    protected function getData($checkId = true)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('semaphore');

        $rootNode
            ->children()
            ->scalarNode('state')->end()
            ->arrayNode('concurrent_ids')
                ->isRequired()
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('checkpoints')
                ->isRequired()
                ->prototype('array')
                    ->prototype('scalar')->isRequired()->end()
                ->end()
            ->end()
        ;

        $data = $this->sharedMemoryHandler->read();

        $processor = new Processor();
        $processor->process($treeBuilder->buildTree(), [$data]);

        if ($checkId && !isset($data['checkpoints'][$this->getId()])) {
            throw new RuntimeException('No checkpoint data found for ID: ' . $this->getId());
        }

        return $data;
    }
}
