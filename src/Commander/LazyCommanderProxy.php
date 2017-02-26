<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Commander;

use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;

/**
 * Commander proxy implementation with lazy initialization.
 *
 * Resolves input strings using heap.
 */
class LazyCommanderProxy implements CommanderInterface
{
    /**
     * @var HeapInterface
     */
    protected $heap;

    /**
     * @var CommanderFactoryInterface
     */
    protected $commanderFactory;

    /**
     * @var CommanderInterface
     */
    protected $commander;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param HeapInterface $heap
     * @param CommanderFactoryInterface $commanderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        HeapInterface $heap,
        CommanderFactoryInterface $commanderFactory,
        LoggerInterface $logger
    ) {
        $this->heap = $heap;
        $this->commanderFactory = $commanderFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        $command = $this->heap->resolveString($command);
        $this->logger->debug('> ' . $command);
        $output = $this->getCommander()->exec($command);
        $output = trim($output);
        $this->logger->debug('< ' . $command . ': ' . $output);
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        $local = $this->heap->resolveString($local);
        $remote = $this->heap->resolveString($remote);
        $this->logger->debug('Send: ' . $local . ' to ' .  $remote);
        $this->getCommander()->send($local, $remote);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $remote = $this->heap->resolveString($remote);
        $local = $this->heap->resolveString($local);
        $this->logger->debug('Recv: ' . $remote . ' to ' .  $local);
        $this->getCommander()->recv($remote, $local);
    }

    /**
     * @return CommanderInterface
     */
    protected function getCommander()
    {
        if (!$this->commander) {
            $this->commander = $this->commanderFactory->create($this->heap->get('connection'));
        }

        return $this->commander;
    }
}
