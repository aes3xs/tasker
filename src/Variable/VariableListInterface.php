<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Variable;

use Aes3xs\Yodler\Exception\VariableAlreadyExistsException;
use Aes3xs\Yodler\Exception\VariableNotFoundException;

/**
 * Interface to variable storage.
 *
 * Variable list provides basic methods to add and retrieve variables by name.
 * There is no method to override already defined variable.
 */
interface VariableListInterface
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
     */
    public function add($name, $value);

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
}
