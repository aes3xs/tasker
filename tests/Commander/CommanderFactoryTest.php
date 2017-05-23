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
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Exception\CommanderAuthenticationException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;

class CommanderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLocalhost()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('localhost');
        
        $sftpFactory = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostLoopback()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('127.0.0.1');

        $sftpFactory = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostNull()
    {
        $connection = new Connection('test');

        $sftpFactory = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateAuthMethodException()
    {
        $this->expectException(\Exception::class);

        $connection = new Connection('test');
        $connection
            ->setHost('unknown');

        $sftpFactory = $this->createMock(SftpFactory::class);
        $factory = new CommanderFactory($sftpFactory);
        $factory->create($connection);
    }

    public function testCreatePhpSecLibForwardingAuth()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setForwarding(true);

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', $this->isInstanceOf(Agent::class))
            ->willReturn(true);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibForwardingAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setForwarding(true);

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', $this->isInstanceOf(Agent::class))
            ->willReturn(false);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $factory->create($connection);
    }

    public function testCreatePhpSecLibKeyAuth()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setKey('key');

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', $this->isInstanceOf(RSA::class))
            ->willReturn(true);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibKeyAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setKey('key');

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', $this->isInstanceOf(RSA::class))
            ->willReturn(false);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $factory->create($connection);
    }

    public function testCreatePhpSecLibPasswordAuth()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setPassword('password');

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', 'password')
            ->willReturn(true);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $commander = $factory->create($connection);

        $this->assertInstanceOf(PhpSecLibCommander::class, $commander);
    }

    public function testCreatePhpSecLibPasswordAuthException()
    {
        $this->expectException(CommanderAuthenticationException::class);

        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setPassword('password');

        $sftp = $this->createMock(SFTP::class);
        $sftp
            ->expects($this->at(0))
            ->method('login')
            ->with('login', 'password')
            ->willReturn(false);
        $sftpFactory = $this->createMock(SftpFactory::class);
        $sftpFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('host', 'port')
            ->willReturn($sftp);
        $factory = new CommanderFactory($sftpFactory);
        $factory->create($connection);
    }
}
