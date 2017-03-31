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

use Aes3xs\Yodler\Variable\VariableListInterface;

/**
 * Scenario implementation.
 */
class Scenario implements ScenarioInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ActionListInterface
     */
    protected $actions;

    /**
     * @var ActionListInterface
     */
    protected $failbackActions;

    /**
     * @var ActionListInterface
     */
    protected $terminateActions;

    /**
     * @var VariableListInterface
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param $name
     * @param ActionListInterface $actions
     * @param ActionListInterface $failbackActions
     * @param ActionListInterface $terminateActions
     * @param VariableListInterface $variables
     */
    public function __construct(
        $name,
        ActionListInterface $actions,
        ActionListInterface $failbackActions,
        ActionListInterface $terminateActions,
        VariableListInterface $variables
    ) {
        $this->name = $name;
        $this->actions = $actions;
        $this->failbackActions = $failbackActions;
        $this->terminateActions = $terminateActions;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFailbackActions()
    {
        return $this->failbackActions;
    }

    /**
     * {@inheritdoc}
     */
    public function getTerminateActions()
    {
        return $this->terminateActions;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
