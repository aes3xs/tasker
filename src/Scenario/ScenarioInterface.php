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
 * Interface to deploy scenario.
 *
 * Scenario contains action definitions to run.
 */
interface ScenarioInterface
{
    /**
     * Return scenario name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return list of actions.
     *
     * @return ActionListInterface
     */
    public function getActions();

    /**
     * Return list of actions to execute on error.
     *
     * @return ActionListInterface
     */
    public function getFailbackActions();

    /**
     * Return list of actions to execute once on scenario termination.
     *
     * @return ActionListInterface
     */
    public function getTerminateActions();

    /**
     * Return list of variables.
     *
     * @return VariableListInterface
     */
    public function getVariables();
}
