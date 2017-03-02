<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Common;

use Aes3xs\Yodler\Exception\ArgumentNotFoundException;

/**
 * Static helper for manipulations with closures.
 */
class CallableHelper
{
    /**
     * Extract call arguments from specified callback.
     *
     * Can be used with array notation [className, methodName].
     *
     * @param callable $callable
     *
     * @return array
     */
    public static function extractArguments(callable $callable)
    {
        $parameters = self::getReflectionParameters($callable);

        $argumentNames = [];
        foreach ($parameters as $parameter) {
            $argumentNames[] = $parameter->getName();
        }

        return $argumentNames;
    }

    /**
     * Execute specified callback with arguments in associative array.
     *
     * @param callable $callable
     * @param array $arguments
     *
     * @return mixed
     */
    public static function call(callable $callable, array $arguments)
    {
        $parameters = self::getReflectionParameters($callable);

        $argumentCallStack = [];
        foreach ($parameters as $parameter) {

            $name = $parameter->getName();

            $hasValue = array_key_exists($name, $arguments);
            $hasDefault = $parameter->isDefaultValueAvailable();
            if (!$hasValue && !$hasDefault) {
                throw new ArgumentNotFoundException($name);
            }

            $argumentCallStack[] = $hasValue ? $arguments[$name] : $parameter->getDefaultValue();
        }

        return call_user_func_array($callable, $argumentCallStack);
    }

    /**
     * @param callable $callable
     *
     * @return \ReflectionParameter[]
     */
    protected static function getReflectionParameters(callable $callable)
    {
        if (is_array($callable)) {
            $class = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            $reflection = new \ReflectionMethod($class, $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable);
        }

        return $reflection->getParameters();
    }
}
