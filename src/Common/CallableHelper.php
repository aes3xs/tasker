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
     * Collects default values into $defaults array.
     *
     * @param callable $callable
     *
     * @param array $defaults
     * @return array
     */
    public static function extractArguments(callable $callable, &$defaults = [])
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
            if ($argument->isDefaultValueAvailable()) {
                $defaults[$argument->getName()] = $argument->getDefaultValue();
            }
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
        $argumentNames = self::extractArguments($callable, $defaults);

        $argumentCallStack = [];

        foreach ($argumentNames as $name) {
            $hasValue = array_key_exists($name, $arguments);
            $hasDefault = array_key_exists($name, $defaults);

            if (!$hasValue && !$hasDefault) {
                throw new ArgumentNotFoundException($name);
            }

            $argumentCallStack[] = $hasValue ? $arguments[$name] : $defaults[$name];
        }

        return call_user_func_array($callable, $argumentCallStack);
    }
}
