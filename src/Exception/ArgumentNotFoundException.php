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
 * This exception is thrown when method argument cannot be found.
 */
class ArgumentNotFoundException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $argumentName;

    /**
     * Constructor.
     * @param string $argumentName
     */
    public function __construct($argumentName)
    {
        parent::__construct(sprintf('Argument not found %s', $argumentName));

        $this->argumentName = $argumentName;
    }

    /**
     * @return string
     */
    public function getArgumentName()
    {
        return $this->argumentName;
    }
}
