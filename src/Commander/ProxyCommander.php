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

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;

class ProxyCommander implements CommanderInterface
{
    /**
     * @var CommanderInterface
     */
    protected $commander;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CommanderFactory
     */
    protected $commanderFactory;

    /**
     * @var HeapInterface
     */
    protected $heap;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param CommanderFactory $commanderFactory
     * @param HeapInterface $heap
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, CommanderFactory $commanderFactory, HeapInterface $heap, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->commanderFactory = $commanderFactory;
        $this->heap = $heap;
        $this->logger = $logger;
    }

    /**
     * @return CommanderInterface
     */
    protected function getCommander()
    {
        if (!$this->commander) {
            $this->commander = $this->commanderFactory->create($this->connection);
        }

        return $this->commander;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        $command = $this->heap->resolveString($command);

        $this->logger->debug('> ' . $command);

        $output = $this->getCommander()->exec($command);

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

        $this->logger->debug('Send: ' . $local . ' to ' . $remote);

        $this->getCommander()->send($local, $remote);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $remote = $this->heap->resolveString($remote);
        $local = $this->heap->resolveString($local);

        $this->logger->debug('Recv: ' . $remote . ' to ' . $local);

        $this->getCommander()->recv($remote, $local);
    }
}
