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

use Aes3xs\Yodler\Exception\VariableAlreadyExistsException;
use Aes3xs\Yodler\Exception\VariableNotFoundException;

/**
 * Interface to implement heap storage.
 *
 * Heap contains multiple variable lists ordered by priority.
 * More prioritized variable will override others with same name.
 */
interface HeapInterface
{
    /**
     * Return all variables in key-indexed array.
     *
     * @return array
     */
    public function all();

    /**
     * Add variable to a list.
     *
     * @param $name
     * @param $value
     *
     * @throws VariableAlreadyExistsException
     */
    public function add($name, $value);

    /**
     * Set variable in a list.
     * If variable already exists, overwrites it.
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value);

    /**
     * Get variable from a list by name.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws VariableNotFoundException
     */
    public function get($name);

    /**
     * Check if list contains variable with specified name.
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name);

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
