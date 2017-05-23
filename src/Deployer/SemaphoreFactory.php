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

use Aes3xs\Yodler\Common\LockableStorage;

/**
 * Semaphore factory.
 */
class SemaphoreFactory
{
    /**
     * Create semaphore instance.
     *
     * @param $lockName
     *
     * @return SemaphoreInterface
     */
    public function create($lockName)
    {
        return new Semaphore(new LockableStorage('semaphore_' . $lockName));
    }
}
