<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Commander;

/**
 * Interface to commander.
 *
 * Commander is actually performs shell commands during deploy.
 */
interface CommanderInterface
{
    /**
     * Execute command.
     *
     * Returns command output or false on error.
     *
     * @param $command
     *
     * @return string|bool
     */
    public function exec($command);

    /**
     * Upload file.
     *
     * @param $local
     * @param $remote
     *
     * @return bool
     */
    public function send($local, $remote);

    /**
     * Download file.
     *
     * @param $remote
     * @param $local
     *
     * @return bool
     */
    public function recv($remote, $local);
}
