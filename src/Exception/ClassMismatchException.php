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
 * This exception is thrown when actual class differs from expected.
 */
class ClassMismatchException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $expectedClass;

    /**
     * @var string
     */
    protected $actualClass;

    /**
     * Constructor.
     *
     * @param string $expectedClass
     * @param int $actualClass
     */
    public function __construct($expectedClass, $actualClass)
    {
        parent::__construct(sprintf('Expected %s is a class or subclass of %s', $actualClass, $expectedClass));

        $this->expectedClass = $expectedClass;
        $this->actualClass = $actualClass;
    }

    /**
     * @return string
     */
    public function getExpectedClass()
    {
        return $this->expectedClass;
    }

    /**
     * @return string
     */
    public function getActualClass()
    {
        return $this->actualClass;
    }
}
