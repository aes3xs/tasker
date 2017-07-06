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

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Scenario\Action;
use Aes3xs\Yodler\Scenario\Scenario;

/**
 * Interface to report.
 *
 * Report is used to collect information about deploy process.
 */
interface ReporterInterface
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
     * Report about deploy.
     *
     * @param Deploy $deploy
     */
    public function reportDeploy(Deploy $deploy);

    /**
     * Report about running action.
     *
     * @param Action $action
     */
    public function reportActionRunning(Action $action);

    /**
     * Report about succeed action.
     *
     * @param Action $action
     * @param $output
     */
    public function reportActionSucceed(Action $action, $output);

    /**
     * Report about error occured while running action.
     *
     * @param Action $action
     * @param \Exception $e
     */
    public function reportActionError(Action $action, \Exception $e);

    /**
     * Report about skipped action.
     *
     * @param Action $action
     */
    public function reportActionSkipped(Action $action);
}
