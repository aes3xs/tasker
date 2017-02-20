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

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ConnectionList;
use Aes3xs\Yodler\Exception\ConnectionAlreadyExistsException;
use Aes3xs\Yodler\Exception\ConnectionNotFoundException;

class ConnectionListTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $list = new ConnectionList();
        $connection1 = $this->createMock(ConnectionInterface::class);
        $connection1->method('getName')->willReturn('test1');
        $connection2 = $this->createMock(ConnectionInterface::class);
        $connection2->method('getName')->willReturn('test2');
        $list->add($connection1);
        $list->add($connection2);

        $this->assertEquals(['test1' => $connection1, 'test2' => $connection2], $list->all());
    }

    public function testAdd()
    {
        $list = new ConnectionList();
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->method('getName')->willReturn('test');
        $list->add($connection);

        $this->assertInstanceOf(ConnectionInterface::class, $list->get('test'));
        $this->assertSame($connection, $list->get('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(ConnectionNotFoundException::class);

        $list = new ConnectionList();

        $list->get('test');
    }

    public function testAlreadyExistException()
    {
        $this->expectException(ConnectionAlreadyExistsException::class);

        $list = new ConnectionList();

        $connection1 = $this->createMock(ConnectionInterface::class);
        $connection1->method('getName')->willReturn('test');
        $connection2 = $this->createMock(ConnectionInterface::class);
        $connection2->method('getName')->willReturn('test');
        $list->add($connection1);
        $list->add($connection2);
    }
}
