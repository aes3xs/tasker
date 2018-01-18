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

use Aes3xs\Yodler\Connection\LocalConnection;
use Aes3xs\Yodler\Connection\ProcessFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class LocalConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $processFactory = $this->createMock(ProcessFactory::class);
        $process = $this->createMock(Process::class);

        $processFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('command')
            ->willReturn($process);

        $process
            ->expects($this->once())
            ->method('mustRun');

        $process
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('output');

        $connection = new LocalConnection($filesystem, $processFactory);

        $output = $connection->exec('command');

        $this->assertEquals('output', $output);
    }

    public function testExecError()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $processFactory = $this->createMock(ProcessFactory::class);
        $process = $this->createMock(Process::class);

        $processFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('command')
            ->willReturn($process);

        $process
            ->expects($this->once())
            ->method('mustRun');

        $process
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('');

        $process
            ->expects($this->once())
            ->method('getErrorOutput')
            ->willReturn('error');

        $connection = new LocalConnection($filesystem, $processFactory);

        $output = $connection->exec('command');

        $this->assertEquals('error', $output);
    }

    public function testSend()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $processFactory = $this->createMock(ProcessFactory::class);

        $filesystem
            ->expects($this->at(0))
            ->method('copy')
            ->with('local', 'remote');

        $connection = new LocalConnection($filesystem, $processFactory);

        $connection->send('local', 'remote');
    }

    public function testRecv()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $processFactory = $this->createMock(ProcessFactory::class);

        $filesystem
            ->expects($this->at(0))
            ->method('copy')
            ->with('remote', 'local');

        $connection = new LocalConnection($filesystem, $processFactory);

        $connection->recv('remote', 'local');
    }
}
