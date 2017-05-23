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
 * Implementation for variable list.
 *
 * Variable list provides basic methods to add and retrieve variables by name.
 * There is no method to override already defined variable.
 */
class VariableList
{
    /**
     * @var array
     */
    protected $variables = [];

    /**
     * Constructor.
     *
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * Return all variables in key-indexed array.
     *
     * @return array
     */
    public function all()
    {
        return $this->variables;
    }

    /**
     * Add variable to a list.
     *
     * @param $name
     * @param $value
     *
     * @throws VariableAlreadyExistsException
     */
    public function add($name, $value)
    {
        if ($this->has($name)) {
            throw new VariableAlreadyExistsException($name);
        }

        $this->variables[$name] = $value;
    }

    /**
     * Set variable in a list.
     * If variable already exists, overwrites it.
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Get variable from a list by name.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws VariableNotFoundException
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new VariableNotFoundException($name);
        }

        return $this->variables[$name];
    }

    /**
     * Check if list contains variable with specified name.
     *
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->variables);
    }
}
