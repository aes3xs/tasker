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

use Aes3xs\Yodler\Common\SharedMemoryHandler;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Report factory implementation.
 */
class ReportFactory implements ReportFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($lockName)
    {
        $lockHandler = new LockHandler($lockName);
        $sharedMemoryHandler = new SharedMemoryHandler($lockName);

        return new Report($lockHandler, $sharedMemoryHandler);
    }
}
