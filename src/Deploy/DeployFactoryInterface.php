<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deploy;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;

/**
 * Interface to deploy factory.
 */
interface DeployFactoryInterface
{
    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $deployConfiguration
     *
     * @return DeployListInterface
     */
    public function createListFromConfiguration($deployConfiguration);

    /**
     * Create stub deploy.
     *
     * @return DeployInterface
     */
    public function createStubDeploy();

    /**
     * Create deploy from scenario and connection.
     *
     * @param ScenarioInterface $scenario
     * @param ConnectionInterface $connection
     *
     * @return DeployInterface
     */
    public function createFromScenarioAndConnection(ScenarioInterface $scenario, ConnectionInterface $connection);
}
