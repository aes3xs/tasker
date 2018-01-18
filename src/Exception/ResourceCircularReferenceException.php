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
 * This exception is thrown when variables depends on itselves directly or through it's variable dependencies.
 */
class ResourceCircularReferenceException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var array
     */
    protected $callstack;

    /**
     * Constructor.
     *
     * @param string $resourceName
     * @param array $callstack
     */
    public function __construct($resourceName, array $callstack)
    {
        parent::__construct(sprintf('Resource circular reference detected %s. Callstack: %s', $resourceName, var_export($callstack, true)));

        $this->resourceName = $resourceName;
        $this->callstack = $callstack;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return array
     */
    public function getCallstack()
    {
        return $this->callstack;
    }
}
