<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Exception;

use Aes3xs\Yodler\Connection\ConnectionInterface;

/**
 * This exception is thrown when commander authentication was failed.
 */
class CommanderAuthenticationException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('Unable to login with provided credentials');
    }

    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        $this->message = sprintf('Unable to login with provided credentials in connection: %s', $connection->getName());
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
