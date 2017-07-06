<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler;

use Aes3xs\Yodler\Exception\ParameterAlreadyExistsException;
use Aes3xs\Yodler\Exception\ParameterNotFoundException;

/**
 * Implementation for parameter list.
 *
 * Parameter list provides basic methods to add and retrieve parameters by name.
 */
class ParameterList
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
     * @throws ParameterAlreadyExistsException
     */
    public function add($name, $value)
    {
        if ($this->has($name)) {
            throw new ParameterAlreadyExistsException($name);
        }

        $this->variables[$name] = $value;
    }

    /**
     * Set variable in a list.
     * If variable already exists, overwrites it.
     *
     * @param $name
     * @param $value
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
     * @throws ParameterNotFoundException
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new ParameterNotFoundException($name);
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
