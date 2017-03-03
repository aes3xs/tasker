<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Scenario;

use Aes3xs\Yodler\Scenario\ActionListInterface;
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\Variable\VariableListInterface;

class ScenarioTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $actionsMock = $this->createMock(ActionListInterface::class);
        $fallbackActionsMock = $this->createMock(ActionListInterface::class);
        $variablesMock = $this->createMock(VariableListInterface::class);

        $scenario = new Scenario('test', $actionsMock, $fallbackActionsMock, $variablesMock);

        $this->assertEquals('test', $scenario->getName());
        $this->assertSame($actionsMock, $scenario->getActions());
        $this->assertSame($fallbackActionsMock, $scenario->getFailbackActions());
        $this->assertSame($variablesMock, $scenario->getVariables());
    }
}
