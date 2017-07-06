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

use Aes3xs\Yodler\Deploy\Deploy;

/**
 * Interface to deploy executor.
 */
interface DeployerInterface
{
    /**
     * Run deploy process.
     *
     * @param Deploy $deploy
     * @param SemaphoreInterface $semaphore
     * @param ReporterInterface $reporter
     */
    public function deploy(Deploy $deploy, SemaphoreInterface $semaphore, ReporterInterface $reporter);
}
