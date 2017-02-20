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
 * Interface to deploy build.
 */
interface BuildInterface
{
    /**
     * Return build scenario.
     *
     * @return ScenarioInterface
     */
    public function getScenario();

    /**
     * Return build connection.
     *
     * @return ConnectionInterface
     */
    public function getConnection();
}
