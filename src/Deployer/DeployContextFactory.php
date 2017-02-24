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

use Aes3xs\Yodler\Connection\ConnectionFactoryInterface;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Deploy\DeployFactoryInterface;
use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Scenario\ScenarioFactoryInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;

/**
 * Deploy context factory implementation.
 */
class DeployContextFactory implements DeployContextFactoryInterface
{
    /**
     * @var DeployFactoryInterface
     */
    protected $deployFactory;

    /**
     * @var ScenarioFactoryInterface
     */
    protected $scenarioFactory;

    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    /**
     * Constructor.
     *
     * @param DeployFactoryInterface $deployFactory
     * @param ScenarioFactoryInterface $scenarioFactory
     * @param ConnectionFactoryInterface $connectionFactory
     */
    public function __construct(
        DeployFactoryInterface $deployFactory,
        ScenarioFactoryInterface $scenarioFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        $this->deployFactory = $deployFactory;
        $this->scenarioFactory = $scenarioFactory;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        DeployInterface $deploy = null,
        ScenarioInterface $scenario = null,
        ConnectionInterface $connection = null
    ) {
        if ($deploy === null) {
            $deploy = $this->deployFactory->createStubDeploy();
        }

        if ($scenario === null) {
            $scenario = $this->scenarioFactory->createStubScenario();
        }

        if ($connection === null) {
            $connection = $this->connectionFactory->createStubConnection();
        }

        return new DeployContext($deploy, $scenario, $connection);
    }
}
