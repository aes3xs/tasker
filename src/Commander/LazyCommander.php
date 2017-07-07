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
use Psr\Log\LoggerInterface;

class LazyCommander implements CommanderInterface
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
     * Constructor.
     *
     * @param Connection $connection
     * @param CommanderFactory $commanderFactory
     */
    public function __construct(Connection $connection, CommanderFactory $commanderFactory)
    {
        $this->commanderFactory = $commanderFactory;
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
    public function setLogger(LoggerInterface $logger)
    {
        $this->getCommander()->setLogger($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        return $this->getCommander()->exec($command);
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        $this->getCommander()->send($local, $remote);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $this->getCommander()->recv($remote, $local);
    }
}
