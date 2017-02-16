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
        if (is_array($callable)) {
            $class = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            $reflection = new \ReflectionMethod($class, $callable[1]);
        } else {
            $reflection = new \ReflectionFunction($callable);
        }

        $arguments = $reflection->getParameters();

        $argumentNames = [];

        foreach ($arguments as $argument) {
            $argumentNames[] = $argument->getName();
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
        $argumentNames = self::extractArguments($callable);

        $argumentCallStack = [];

        foreach ($argumentNames as $name) {
            if (!array_key_exists($name, $arguments)) {
                throw new ArgumentNotFoundException($name);
            }

            $argumentCallStack[] = $arguments[$name];
        }

        return call_user_func_array($callable, $argumentCallStack);
    }
}
