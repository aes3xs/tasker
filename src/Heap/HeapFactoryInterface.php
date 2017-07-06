<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Scenario\Scenario;
use Psr\Log\LoggerInterface;

/**
 * Interface to heap factory.
 */
interface HeapFactoryInterface
{
    /**
     * Create and return deploy heap.
     *
     * @param Deploy $deploy
     * @param LoggerInterface $logger
     *
     * @return HeapInterface
     */
    public function create(Deploy $deploy, LoggerInterface $logger);
}
