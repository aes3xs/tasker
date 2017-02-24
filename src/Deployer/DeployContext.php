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

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;

/**
 * Deploy context implementation.
 */
class DeployContext implements DeployContextInterface
{
    /**
     * @var DeployInterface
     */
    protected $deploy;

    /**
     * @var ScenarioInterface
     */
    protected $scenario;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param DeployInterface $deploy
     * @param ScenarioInterface $scenario
     * @param ConnectionInterface $connection
     */
    public function __construct(
        DeployInterface $deploy,
        ScenarioInterface $scenario,
        ConnectionInterface $connection
    ) {
        $this->deploy = $deploy;
        $this->scenario = $scenario;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeploy()
    {
        return $this->deploy;
    }

    /**
     * {@inheritdoc}
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
