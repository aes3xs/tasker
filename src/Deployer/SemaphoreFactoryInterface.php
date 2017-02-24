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

/**
 * Interface to semaphore factory.
 */
interface SemaphoreFactoryInterface
{
    /**
     * Create semaphore instance.
     *
     * @param $lockName
     *
     * @return Semaphore
     */
    public function create($lockName);
}
