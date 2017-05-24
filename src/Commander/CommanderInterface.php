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

use Psr\Log\LoggerInterface;

/**
 * Interface to commander.
 *
 * Commander is actually performs shell commands during deploy.
 */
interface CommanderInterface
{
    /**
     * Set debug logger.
     *
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger);

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
