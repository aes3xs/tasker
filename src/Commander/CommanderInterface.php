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
     * @param $command
     */
    public function exec($command);

    /**
     * Upload file.
     *
     * @param $local
     * @param $remote
     */
    public function upload($local, $remote);

    /**
     * Download file.
     *
     * @param $remote
     * @param $local
     */
    public function download($remote, $local);
}
