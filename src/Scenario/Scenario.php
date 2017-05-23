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
use Aes3xs\Yodler\Variable\VariableList;

/**
 * Scenario implementation.
 *
 * Scenario contains action definitions to run.
 */
class Scenario
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Action[]
     */
    protected $actions = [];

    /**
     * @var Action[]
     */
    protected $failbacks = [];

    /**
     * @var VariableList
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Return scenario name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

    /**
     * @return VariableList
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param VariableList $variables
     *
     * @return $this
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }
}
