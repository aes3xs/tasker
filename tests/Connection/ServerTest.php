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

use Aes3xs\Yodler\Connection\Server;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $server = new Server('host', 1122);

        $this->assertEquals('host', $server->getHost());
        $this->assertEquals(1122, $server->getPort());
    }
}
