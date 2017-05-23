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

use Aes3xs\Yodler\Connection\Connection;

/**
 * This exception is thrown when commander authentication was failed.
 */
class CommanderAuthenticationException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('Unable to login with provided credentials');
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        $this->message = sprintf('Unable to login with provided credentials in connection: %s', $connection->getName());
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
