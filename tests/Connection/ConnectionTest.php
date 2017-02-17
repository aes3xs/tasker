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
use Aes3xs\Yodler\Connection\Server;
use Aes3xs\Yodler\Connection\User;
use Aes3xs\Yodler\Variable\VariableList;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $server = new Server(null, null);
        $user = new User(null);
        $variables = new VariableList();
        $connection = new Connection('test', $server, $user, $variables);

        $this->assertEquals('test', $connection->getName());
        $this->assertSame($server, $connection->getServer());
        $this->assertSame($user, $connection->getUser());
        $this->assertSame($variables, $connection->getVariables());
    }
}
