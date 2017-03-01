<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Event;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event invoked before deploy.
 */
class DeployEvent extends Event
{
    const NAME = 'deploy';

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
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
