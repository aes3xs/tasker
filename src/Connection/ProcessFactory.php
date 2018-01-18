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

use Symfony\Component\Process\Process;

/**
 * Symfony process factory.
 */
class ProcessFactory
{
    /**
     * Create symfony process instance.
     *
     * @param $command
     *
     * @return Process
     */
    public function create($command)
    {
        return new Process($command);
    }
}
