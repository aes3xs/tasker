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

/**
 * This exception is thrown when connection with the provided name doesn't exist in the list.
 */
class ConnectionNotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $connectionName;

    /**
     * Constructor.
     * @param string $connectionName
     */
    public function __construct($connectionName)
    {
        parent::__construct(sprintf('Connection "%s" not found', $connectionName));

        $this->connectionName = $connectionName;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }
}
