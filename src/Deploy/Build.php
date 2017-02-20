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
 * Build implementation.
 */
class Build implements BuildInterface
{
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
     * @param ScenarioInterface $scenario
     * @param ConnectionInterface $connection
     */
    public function __construct(ScenarioInterface $scenario, ConnectionInterface $connection)
    {
        $this->scenario = $scenario;
        $this->connection = $connection;
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
