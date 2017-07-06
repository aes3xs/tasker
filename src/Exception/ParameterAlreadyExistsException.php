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
 * This exception is thrown when trying to add variable to a list, which already contains variable with the same name.
 */
class ParameterAlreadyExistsException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $variableName;

    /**
     * Constructor.
     * @param string $variableName
     */
    public function __construct($variableName)
    {
        parent::__construct(sprintf('Variable already exists %s', $variableName));

        $this->variableName = $variableName;
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }
}
