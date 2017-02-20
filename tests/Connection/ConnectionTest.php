<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Connection;

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Connection\ServerInterface;
use Aes3xs\Yodler\Connection\UserInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $serverMock = $this->createMock(ServerInterface::class);
        $userMock = $this->createMock(UserInterface::class);
        $variablesMock = $this->createMock(VariableListInterface::class);
        $connection = new Connection('test', $serverMock, $userMock, $variablesMock);

        $this->assertEquals('test', $connection->getName());
        $this->assertSame($serverMock, $connection->getServer());
        $this->assertSame($userMock, $connection->getUser());
        $this->assertSame($variablesMock, $connection->getVariables());
    }
}
