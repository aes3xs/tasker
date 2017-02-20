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
use Aes3xs\Yodler\Deploy\BuildListInterface;
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Variable\VariableListInterface;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $buildsMock = $this->createMock(BuildListInterface::class);
        $actionsMock = $this->createMock(ActionListInterface::class);
        $variablesMock = $this->createMock(VariableListInterface::class);
        $deploy = new Deploy('test', $buildsMock, $actionsMock, $variablesMock);

        $this->assertEquals('test', $deploy->getName());
        $this->assertSame($buildsMock, $deploy->getBuilds());
        $this->assertSame($actionsMock, $deploy->getDoneActions());
        $this->assertSame($variablesMock, $deploy->getVariables());
    }
}
