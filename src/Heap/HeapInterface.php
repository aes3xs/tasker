<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

/**
 * Interface to implement heap storage.
 *
 * Heap contains multiple variable lists ordered by priority.
 * More prioritized variable will override others with same name.
 */
interface HeapInterface
{
    /**
     * Check if contains variable with specified name.
     *
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * Get variable value.
     *
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * Resolve string using twig syntax.
     *
     * @param $string
     * @return string
     */
    public function resolveString($string);

    /**
     * Resolve string containing symfony expression language statement.
     *
     * @param $expression
     * @return mixed
     */
    public function resolveExpression($expression);

    /**
     * Resolve callback.
     *
     * @param callable $callback
     * @return mixed
     */
    public function resolveCallback(callable $callback);
}
