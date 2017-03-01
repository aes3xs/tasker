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

use Aes3xs\Yodler\Connection\ConnectionListInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;

/**
 * Interface to deployer.
 */
interface DeployerInterface
{
    /**
     * Run deploy process.
     *
     * @param ScenarioInterface $scenario
     * @param ConnectionListInterface $connections
     *
     * @return bool
     */
    public function deploy(ScenarioInterface $scenario, ConnectionListInterface $connections);
}
