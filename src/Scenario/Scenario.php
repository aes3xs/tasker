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
 * Scenario implementation.
 *
 * Scenario contains action definitions to run.
 */
class Scenario
{
    /**
     * @var Action[]
     */
    protected $actions = [];

    /**
     * @var Action[]
     */
    protected $failbacks = [];

    /**
     * @var callable
     */
    protected $initializer;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set initializer callback.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setInitializer(callable $callback)
    {
        $this->initializer = $callback;

        return $this;
    }

    /**
     * Return initializer callback.
     *
     * @return callable
     */
    public function getInitializer()
    {
        return $this->initializer;
    }

    /**
     * Return list of actions.
     *
     * @return Action[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add action.
     *
     * @param Action $action
     *
     * @return $this
     */
    public function addAction(Action $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Return list of actions to execute on error.
     *
     * @return Action[]
     */
    public function getFailbacks()
    {
        return $this->failbacks;
    }

    /**
     * Add failback action.
     *
     * @param Action $action
     *
     * @return $this
     */
    public function addFailback(Action $action)
    {
        $this->failbacks[] = $action;

        return $this;
    }
}
