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

use Aes3xs\Yodler\Exception\ErrorInterruptException;
use Aes3xs\Yodler\Exception\TimeoutInterruptException;

/**
 * Interface to semaphore manager.
 *
 * Semaphore is used to coordinate and synchronize parallel processes.
 * It's work based on checkpoint reporting.
 */
interface SemaphoreInterface
{
    /**
     * Reset semaphore to initial state.
     */
    public function reset();

    /**
     * Add process PID to semaphore regulation.
     *
     * @param $pid
     */
    public function addProcess($pid);

    /**
     * Trigger processes execution.
     *
     * All processes will start simultaneously.
     */
    public function run();

    /**
     * Initialize and confirm process ready to start.
     */
    public function reportReady();

    /**
     * Report about reaching checkpoint.
     *
     * @param $name
     *
     * @throws ErrorInterruptException
     * @throws TimeoutInterruptException
     */
    public function reportCheckpoint($name);

    /**
     * Report about error occured during execution.
     */
    public function reportError();
}
