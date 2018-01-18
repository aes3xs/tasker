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
use Aes3xs\Yodler\Connection\ConnectionFactory;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ConnectionParameters;
use Aes3xs\Yodler\Resolver\ResourceResolver;
use Psr\Log\LoggerInterface;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testNoParameters()
    {
        $this->expectException(\Exception::class);

        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $resourceResolver = $this->createMock(ResourceResolver::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connection = new Connection($connectionFactory, $resourceResolver, $logger);

        $connection->exec('exec');
    }

    public function testInternalConnectionInit()
    {
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $resourceResolver = $this->createMock(ResourceResolver::class);
        $logger = $this->createMock(LoggerInterface::class);
        $connectionParameters = $this->createMock(ConnectionParameters::class);
        $internalConnection = $this->createMock(ConnectionInterface::class);

        $connectionFactory
            ->expects($this->once())
            ->method('create')
            ->with($connectionParameters)
            ->willReturn($internalConnection);

        $internalConnection
            ->expects($this->at(0))
            ->method('exec');

        $internalConnection
            ->expects($this->at(1))
            ->method('exec');

        $connection = new Connection($connectionFactory, $resourceResolver, $logger);
        $connection->setParameters($connectionParameters);

        $connection->exec('exec');
        $connection->exec('exec');
    }

    public function testExec()
    {
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $resourceResolver = $this->createMock(ResourceResolver::class);
        $logger = $this->createMock(LoggerInterface::class);
        $connectionParameters = $this->createMock(ConnectionParameters::class);
        $internalConnection = $this->createMock(ConnectionInterface::class);

        $resourceResolver
            ->expects($this->at(0))
            ->method('resolveString')
            ->with('exec')
            ->willReturn('resolved exec');

        $connectionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($internalConnection);

        $internalConnection
            ->expects($this->at(0))
            ->method('exec')
            ->with('resolved exec')
            ->willReturn('exec output');

        $connection = new Connection($connectionFactory, $resourceResolver, $logger);
        $connection->setParameters($connectionParameters);

        $output = $connection->exec('exec');

        $this->assertEquals('exec output', $output);
    }

    public function testSend()
    {
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $resourceResolver = $this->createMock(ResourceResolver::class);
        $logger = $this->createMock(LoggerInterface::class);
        $connectionParameters = $this->createMock(ConnectionParameters::class);
        $internalConnection = $this->createMock(ConnectionInterface::class);

        $resourceResolver
            ->expects($this->at(0))
            ->method('resolveString')
            ->with('local')
            ->willReturn('resolved local');

        $resourceResolver
            ->expects($this->at(1))
            ->method('resolveString')
            ->with('remote')
            ->willReturn('resolved remote');

        $connectionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($internalConnection);

        $internalConnection
            ->expects($this->at(0))
            ->method('send')
            ->with('resolved local', 'resolved remote');

        $connection = new Connection($connectionFactory, $resourceResolver, $logger);
        $connection->setParameters($connectionParameters);

        $connection->send('local', 'remote');
    }

    public function testRecv()
    {
        $connectionFactory = $this->createMock(ConnectionFactory::class);
        $resourceResolver = $this->createMock(ResourceResolver::class);
        $logger = $this->createMock(LoggerInterface::class);
        $connectionParameters = $this->createMock(ConnectionParameters::class);
        $internalConnection = $this->createMock(ConnectionInterface::class);

        $resourceResolver
            ->expects($this->at(0))
            ->method('resolveString')
            ->with('remote')
            ->willReturn('resolved remote');

        $resourceResolver
            ->expects($this->at(1))
            ->method('resolveString')
            ->with('local')
            ->willReturn('resolved local');

        $connectionFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($internalConnection);

        $internalConnection
            ->expects($this->at(0))
            ->method('recv')
            ->with('resolved remote', 'resolved local');

        $connection = new Connection($connectionFactory, $resourceResolver, $logger);
        $connection->setParameters($connectionParameters);

        $connection->recv('remote', 'local');
    }
}
