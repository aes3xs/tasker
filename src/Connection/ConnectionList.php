<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Connection;

use Aes3xs\Yodler\Exception\ConnectionAlreadyExistsException;
use Aes3xs\Yodler\Exception\ConnectionNotFoundException;

/**
 * Default implementation for connection list.
 */
class ConnectionList implements ConnectionListInterface
{
    /**
     * @var ConnectionInterface[]
     */
    protected $connections = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->connections;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ConnectionInterface $connection)
    {
        if (isset($this->connections[$connection->getName()])) {
            throw new ConnectionAlreadyExistsException($connection->getName());
        }

        $this->connections[$connection->getName()] = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!isset($this->connections[$name])) {
            throw new ConnectionNotFoundException($name);
        }

        return $this->connections[$name];
    }
}
