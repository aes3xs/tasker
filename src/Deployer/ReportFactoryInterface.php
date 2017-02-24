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
 * Interface to report factory.
 */
interface ReportFactoryInterface
{
    /**
     * Create report instance.
     *
     * @param $lockName
     *
     * @return ReportInterface
     */
    public function create($lockName);
}
