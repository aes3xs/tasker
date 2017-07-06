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
 * This exception is thrown when variables depends on itselves directly or through it's variable dependencies.
 */
class ParameterCircularReferenceException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $variableName;

    /**
     * @var array
     */
    protected $variableCallStack;

    /**
     * Constructor.
     *
     * @param string $variableName
     * @param array $variableCallStack
     */
    public function __construct($variableName, array $variableCallStack)
    {
        parent::__construct(sprintf('Variable circular reference detected %s. Callstack: %s', $variableName, var_export($variableCallStack, true)));

        $this->variableName = $variableName;
        $this->variableCallStack = $variableCallStack;
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @return array
     */
    public function getVariableCallStack()
    {
        return $this->variableCallStack;
    }
}
