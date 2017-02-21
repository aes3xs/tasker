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

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Exception\CommanderAuthenticationException;

/**
 * Interface to commander factory.
 */
interface CommanderFactoryInterface
{
    /**
     * Create commander from connection definition.
     *
     * @param ConnectionInterface $connection
     *
     * @return CommanderInterface
     *
     * @throws CommanderAuthenticationException
     */
    public function create(ConnectionInterface $connection);
}
