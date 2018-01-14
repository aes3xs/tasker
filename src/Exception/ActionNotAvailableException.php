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
 * This exception is thrown when action cannot be executed.
 */
class ActionNotAvailableException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $actionName;

    /**
     * Constructor.
     * @param string $actionName
     */
    public function __construct($actionName)
    {
        parent::__construct(sprintf('Action not available %s', $actionName));

        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }
}
