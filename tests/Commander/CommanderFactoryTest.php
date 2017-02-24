<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Commander;

use Aes3xs\Yodler\Commander\CommanderFactory;
use Aes3xs\Yodler\Commander\LocalCommander;
use Aes3xs\Yodler\Commander\PhpSecLibCommander;
use Aes3xs\Yodler\Commander\SftpFactory;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ServerInterface;
use Aes3xs\Yodler\Connection\UserInterface;
use Aes3xs\Yodler\Exception\CommanderAuthenticationException;
use Aes3xs\Yodler\Exception\RuntimeException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;

class CommanderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLocalhost()
    {
        $connectionMock = $this->createConnectionMock('localhost');
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostLoopback()
    {
        $connectionMock = $this->createConnectionMock('127.0.0.1');
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostNull()
    {
        $connectionMock = $this->createConnectionMock(null);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateAuthMethodException()
    {
        $this->expectException(RuntimeException::class);

        $connectionMock = $this->createConnectionMock('unknown');
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactoryMock);
        $factory->create($connectionMock);
    }

    public function testCreatePhpSecLibForwardingAuth()
    {
        $connectionMock = $this->createConnectionMock('host', 'port', 'login', null, null, null, true);
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', $this->isInstanceOf(Agent::class))->willReturn(true);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibForwardingAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connectionMock = $this->createConnectionMock('host', 'port', 'login', null, null, null, true);
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', $this->isInstanceOf(Agent::class))->willReturn(false);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibKeyAuth()
    {
        $connectionMock = $this->createConnectionMock('host', 'port', 'login', null, 'key');
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', $this->isInstanceOf(RSA::class))->willReturn(true);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibKeyAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connectionMock = $this->createConnectionMock('host', 'port', 'login', null, 'key');
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', $this->isInstanceOf(RSA::class))->willReturn(false);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibPasswordAuth()
    {
        $connectionMock = $this->createConnectionMock('host', 'port', 'login', 'password');
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', 'password')->willReturn(true);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibPasswordAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connectionMock = $this->createConnectionMock('host', 'port', 'login', 'password');
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('login')->with('login', 'password')->willReturn(false);
        $sftpFactoryMock = $this->createMock(SftpFactory::class);
        $sftpFactoryMock->method('create')->with('host', 'port')->willReturn($sftpMock);
        $factory = new CommanderFactory($sftpFactoryMock);
        $commander = $factory->create($connectionMock);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    protected function createConnectionMock(
        $host = null,
        $port = null,
        $login = null,
        $password = null,
        $key = null,
        $passphrase = null,
        $forwarding = false
    ) {
        $serverMock = $this->createMock(ServerInterface::class);
        $serverMock->method('getHost')->willReturn($host);
        $serverMock->method('getPort')->willReturn($port);
        $userMock = $this->createMock(UserInterface::class);
        $userMock->method('getLogin')->willReturn($login);
        $userMock->method('getPassword')->willReturn($password);
        $userMock->method('getKey')->willReturn($key);
        $userMock->method('getPassphrase')->willReturn($passphrase);
        $userMock->method('getForwarding')->willReturn($forwarding);
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('getServer')->willReturn($serverMock);
        $connectionMock->method('getUser')->willReturn($userMock);
        $connectionMock->method('getName')->willReturn('test');

        return $connectionMock;
    }
}
