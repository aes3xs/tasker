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
 * Semaphore is used to control parallel deploy execution.
 * It's work based on checkpoint reporting.
 */
interface SemaphoreInterface
{
    /**
     * Reset semaphore to initial state.
     */
    public function reset();

    /**
     * Start deploy with passed builds IDs.
     *
     * All processes with passed IDs will start simultaneously.
     *
     * @param array $concurrentIds
     */
    public function run(array $concurrentIds);

    /**
     * Initialize and confirm deploy ready to start.
     *
     * Every deploy process must report about itself and pass it's ID.
     *
     * @param $id
     */
    public function reportReady($id);

    /**
     * Report about reaching deploy checkpoint.
     *
     * @param $name
     *
     * @throws ErrorInterruptException
     * @throws TimeoutInterruptException
     */
    public function reportCheckpoint($name);

    /**
     * Report about error occured during deploy.
     */
    public function reportError();
}
