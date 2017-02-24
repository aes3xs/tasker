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

use Aes3xs\Yodler\Commander\LocalCommander;
use Aes3xs\Yodler\Common\ProcessFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class LocalCommanderTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $filesystemMock = $this->createMock(Filesystem::class);
        $processMock = $this->createMock(Process::class);
        $processMock->method('mustRun');
        $processFactoryMock = $this->createMock(ProcessFactory::class);
        $processFactoryMock->method('create')->with('command')->willReturn($processMock);

        $commander = new LocalCommander($filesystemMock, $processFactoryMock);

        $commander->exec('command');
    }

    public function testSend()
    {
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->method('copy')->with('local', 'remote');
        $processFactoryMock = $this->createMock(ProcessFactory::class);

        $commander = new LocalCommander($filesystemMock, $processFactoryMock);

        $commander->send('local', 'remote');
    }

    public function testRecv()
    {
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->method('copy')->with('remote', 'local');
        $processFactoryMock = $this->createMock(ProcessFactory::class);

        $commander = new LocalCommander($filesystemMock, $processFactoryMock);

        $commander->recv('remote', 'local');
    }
}
