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

use Aes3xs\Yodler\Commander\CommanderFactoryInterface;
use Aes3xs\Yodler\Commander\CommanderInterface;
use Aes3xs\Yodler\Commander\LazyCommanderProxy;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;

class LazyCommanderProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock->method('get')->with('connection')->willReturn($connectionMock);
        $heapMock->method('resolveString')->with('command')->willReturn('resolvedCommand');
        $commanderMock = $this->createMock(CommanderInterface::class);
        $commanderMock->method('exec')->with('resolvedCommand')->willReturn('output');
        $commanderFactoryMock = $this->createMock(CommanderFactoryInterface::class);
        $commanderFactoryMock->method('create')->with($connectionMock)->willReturn($commanderMock);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $commander = new LazyCommanderProxy($heapMock, $commanderFactoryMock, $loggerMock);

        $this->assertEquals('output', $commander->exec('command'));
    }

    public function testSend()
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock->method('get')->with('connection')->willReturn($connectionMock);
        $heapMock->expects($this->at(0))->method('resolveString')->with('local')->willReturn('resolvedLocal');
        $heapMock->expects($this->at(1))->method('resolveString')->with('remote')->willReturn('resolvedRemote');
        $commanderMock = $this->createMock(CommanderInterface::class);
        $commanderMock->method('send')->with('resolvedLocal', 'resolvedRemote');
        $commanderFactoryMock = $this->createMock(CommanderFactoryInterface::class);
        $commanderFactoryMock->method('create')->with($connectionMock)->willReturn($commanderMock);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $commander = new LazyCommanderProxy($heapMock, $commanderFactoryMock, $loggerMock);

        $commander->send('local', 'remote');
    }

    public function testRecv()
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock->method('get')->with('connection')->willReturn($connectionMock);
        $heapMock->expects($this->at(0))->method('resolveString')->with('remote')->willReturn('resolvedRemote');
        $heapMock->expects($this->at(1))->method('resolveString')->with('local')->willReturn('resolvedLocal');
        $commanderMock = $this->createMock(CommanderInterface::class);
        $commanderMock->method('recv')->with('resolvedRemote', 'resolvedLocal');
        $commanderFactoryMock = $this->createMock(CommanderFactoryInterface::class);
        $commanderFactoryMock->method('create')->with($connectionMock)->willReturn($commanderMock);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $commander = new LazyCommanderProxy($heapMock, $commanderFactoryMock, $loggerMock);

        $commander->recv('remote', 'local');
    }
}
