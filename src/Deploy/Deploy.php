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

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\ParameterList;

/**
 * Deploy implementation.
 *
 * Deploy keeps info about connection, scenario and build parameters.
 */
class Deploy
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Scenario
     */
    protected $scenario;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ParameterList
     */
    protected $parameters;

    /**
     * Constructor.
     *
     * @param $name
     * @param Scenario $scenario
     * @param Connection $connection
     */
    public function __construct($name, Scenario $scenario, Connection $connection)
    {
        $this->name = $name;
        $this->scenario = $scenario;
        $this->connection = $connection;
        $this->parameters = new ParameterList();
    }

    /**
     * Return scenario name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return ParameterList
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
