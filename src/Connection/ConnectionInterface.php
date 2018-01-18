<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Connection;

/**
 * Connection interface to different connection types.
 */
interface ConnectionInterface
{
    /**
     * Execute command.
     *
     * Returns command output or throws exception on error.
     *
     * @param $command
     *
     * @return string
     */
    public function exec($command);

    /**
     * Upload file.
     *
     * @param $local
     * @param $remote
     */
    public function send($local, $remote);

    /**
     * Download file.
     *
     * @param $remote
     * @param $local
     */
    public function recv($remote, $local);
}
