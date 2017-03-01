<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deployer;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Scenario\ActionInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;

/**
 * Interface to report.
 *
 * Report is used to collect information about deploy process.
 */
interface ReportInterface
{
    const ACTION_STATE_NONE = 'None';
    const ACTION_STATE_SKIPPED = 'Skipped';
    const ACTION_STATE_RUNNING = 'Running';
    const ACTION_STATE_SUCCEED = 'Succeed';
    const ACTION_STATE_ERROR = 'Error';

    /**
     * Reset report state.
     */
    public function reset();

    /**
     * Initialize report instance with given ID.
     *
     * @param $id
     */
    public function initialize($id);

    /**
     * Report about deploy.
     *
     * @param ScenarioInterface $scenario
     * @param ConnectionInterface $connection
     */
    public function reportDeploy(ScenarioInterface $scenario, ConnectionInterface $connection);

    /**
     * Report about running action.
     *
     * @param ActionInterface $action
     */
    public function reportActionRunning(ActionInterface $action);

    /**
     * Report about succeed action.
     *
     * @param ActionInterface $action
     * @param $output
     */
    public function reportActionSucceed(ActionInterface $action, $output);

    /**
     * Report about error occured while running action.
     *
     * @param ActionInterface $action
     * @param \Exception $e
     */
    public function reportActionError(ActionInterface $action, \Exception $e);

    /**
     * Report about skipped action.
     *
     * @param ActionInterface $action
     */
    public function reportActionSkipped(ActionInterface $action);

    /**
     * Get report data.
     */
    public function getRawData();
}
