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
 * Default implementation for variable list.
 */
class VariableList implements VariableListInterface
{
    /**
     * @var VariableInterface[]
     */
    protected $variables = [];

    /**
     * Constructor.
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        foreach ($variables as $variable) {
            $this->add($variable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->variables;
    }

    /**
     * {@inheritdoc}
     */
    public function add(VariableInterface $variable)
    {
        if ($this->has($variable->getName())) {
            throw new VariableAlreadyExistsException($variable->getName());
        }

        $this->variables[$variable->getName()] = $variable;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new VariableNotFoundException($name);
        }

        return $this->variables[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->variables[$name]);
    }
}
