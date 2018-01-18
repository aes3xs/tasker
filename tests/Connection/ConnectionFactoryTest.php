<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Tests\Connection;

use Aes3xs\Tasker\Connection\ConnectionFactory;
use Aes3xs\Tasker\Connection\LocalConnection;
use Aes3xs\Tasker\Connection\PhpSecLibClientFactory;
use Aes3xs\Tasker\Connection\PhpSecLibConnection;
use Aes3xs\Tasker\Connection\ConnectionParameters;
use phpseclib\Net\SFTP;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLocalhost()
    {
        $params = new ConnectionParameters();
        $params
            ->setHost('localhost');

        $factory = new ConnectionFactory(new PhpSecLibClientFactory());
        $connection = $factory->create($params);

        $this->assertInstanceOf(LocalConnection::class, $connection);
    }

    public function testCreateAuthMethodException()
    {
        $this->expectException(\Exception::class);

        $params = new ConnectionParameters();
        $params
            ->setHost('unknown');

        $factory = new ConnectionFactory(new PhpSecLibClientFactory());
        $factory->create($params);
    }

    public function testCreateSshConnection()
    {
        $params = new ConnectionParameters();
        $params
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setPassword('password');

        $sshFactory = $this->createMock(PhpSecLibClientFactory::class);
        $sshFactory
            ->expects($this->once())
            ->method('createPasswordAuthClient')
            ->willReturn($this->createMock(SFTP::class));

        $factory = new ConnectionFactory($sshFactory);
        $connection = $factory->create($params);

        $this->assertInstanceOf(PhpSecLibConnection::class, $connection);
    }
}
