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

/**
 * This exception is thrown when trying to get a resource that doesn't exist.
 */
class ResourceNotFoundException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $resourceName;

    /**
     * Constructor.
     * @param string $resourceName
     */
    public function __construct($resourceName)
    {
        parent::__construct(sprintf('Resource not found %s', $resourceName));

        $this->resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }
}
