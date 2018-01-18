<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Exception;

use Aes3xs\Tasker\Connection\ConnectionParameters;

/**
 * This exception is thrown when connection authentication was failed.
 */
class ConnectionAuthenticationException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var ConnectionParameters
     */
    protected $connectionParameters;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('Unable to login with provided credentials');
    }

    public function setConnectionParameters(ConnectionParameters $connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;

        $this->message = sprintf('Unable to login with provided credentials in connection');
    }

    /**
     * @return ConnectionParameters
     */
    public function getConnectionParameters()
    {
        return $this->connectionParameters;
    }
}
