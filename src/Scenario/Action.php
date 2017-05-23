<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Scenario;

/**
 * Action implementation.
 */
class Action
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string|null
     */
    protected $condition;

    /**
     * Constructor.
     *
     * @param $name
     * @param callable $callback
     * @param null $condition
     */
    public function __construct($name, callable $callback, $condition = null)
    {
        $this->name = $name;
        $this->callback = $callback;
        $this->condition = $condition;
    }

    /**
     * Return name for action.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return callback.
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Return condition.
     *
     * @return string|null
     */
    public function getCondition()
    {
        return $this->condition;
    }
}
