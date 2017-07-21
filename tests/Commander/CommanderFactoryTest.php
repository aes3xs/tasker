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
use Aes3xs\Yodler\Commander\SshExtensionCommander;
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Exception\CommanderAuthenticationException;

class CommanderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLocalhost()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('localhost');

        $factory = new CommanderFactory();
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostLoopback()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('127.0.0.1');

        $factory = new CommanderFactory();
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateLocalhostNull()
    {
        $connection = new Connection('test');

        $factory = new CommanderFactory();
        $commander = $factory->create($connection);

        $this->assertInstanceOf(LocalCommander::class, $commander);
    }

    public function testCreateAuthMethodException()
    {
        $this->expectException(\Exception::class);

        $connection = new Connection('test');
        $connection
            ->setHost('unknown');

        $factory = new CommanderFactory();
        $factory->create($connection);
    }

    public function testCreateSshExtension()
    {
        $connection = new Connection('test');
        $connection
            ->setHost('host')
            ->setPort('port')
            ->setLogin('login')
            ->setForwarding(true);

        $factory = new CommanderFactory();
        $commander = $factory->create($connection);

        $this->assertInstanceOf(SshExtensionCommander::class, $commander);
    }
}
