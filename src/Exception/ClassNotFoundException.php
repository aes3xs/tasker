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
 * This exception is thrown when specified class cannot be found.
 */
class ClassNotFoundException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * Constructor.
     * @param string $className
     */
    public function __construct($className)
    {
        parent::__construct(sprintf('Class not found %s', $className));

        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
