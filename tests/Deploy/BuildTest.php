<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Deploy;

use Aes3xs\Yodler\Action\ActionListInterface;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Deploy\Build;
use Aes3xs\Yodler\Deploy\BuildListInterface;
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;

class BuildTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $build = new Build($scenarioMock, $connectionMock);

        $this->assertSame($scenarioMock, $build->getScenario());
        $this->assertSame($connectionMock, $build->getConnection());
    }
}
