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
 * Interface to connection list.
 */
interface ConnectionListInterface
{
    /**
     * Return all connections in key-indexed array.
     *
     * @return ConnectionInterface[]
     */
    public function all();

    /**
     * Add connection to a list.
     *
     * @param ConnectionInterface $connection
     *
     * @throws ConnectionAlreadyExistsException
     */
    public function add(ConnectionInterface $connection);

    /**
     * Get connection from a list by name.
     *
     * @param $name
     *
     * @return ConnectionInterface
     *
     * @throws ConnectionNotFoundException
     */
    public function get($name);
}
