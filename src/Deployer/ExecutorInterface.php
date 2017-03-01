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

use Aes3xs\Yodler\Scenario\ActionListInterface;

/**
 * Interface to action executor.
 */
interface ExecutorInterface
{
    /**
     * Execute action list.
     *
     * @param ActionListInterface $actions
     */
    public function execute(ActionListInterface $actions);
}
