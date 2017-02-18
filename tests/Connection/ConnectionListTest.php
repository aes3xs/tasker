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
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ConnectionList;
use Aes3xs\Yodler\Connection\Server;
use Aes3xs\Yodler\Connection\User;
use Aes3xs\Yodler\Exception\ConnectionAlreadyExistsException;
use Aes3xs\Yodler\Exception\ConnectionNotFoundException;
use Aes3xs\Yodler\Variable\VariableList;

class ConnectionListTest extends \PHPUnit_Framework_TestCase
{
    protected function createConnection($name)
    {
        $server = new Server(null, null);
        $user = new User(null);
        $variables = new VariableList();
        
        return new Connection($name, $server, $user, $variables);
    }

    public function testAll()
    {
        $list = new ConnectionList();
        $connection1 = $this->createConnection('test1');
        $connection2 = $this->createConnection('test2');
        $list->add($connection1);
        $list->add($connection2);

        $this->assertEquals(['test1' => $connection1, 'test2' => $connection2], $list->all());
    }

    public function testAdd()
    {
        $list = new ConnectionList();
        $connection = $this->createConnection('test');
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

        $list->add($this->createConnection('test'));
        $list->add($this->createConnection('test'));
    }
}
