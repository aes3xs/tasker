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

use Aes3xs\Yodler\Commander\PhpSecLibCommander;
use Aes3xs\Yodler\Exception\PhpSecLibCommandException;
use phpseclib\Net\SFTP;

class PhpSecLibCommanderTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(1))
            ->method('exec')
            ->with('command')
            ->willReturn('output');
        $sftpMock
            ->expects($this->at(2))
            ->method('getExitStatus')
            ->willReturn(0);

        $commander = new PhpSecLibCommander($sftpMock);

        $this->assertEquals('output', $commander->exec('command'));
    }

    public function testExecException()
    {
        $this->expectException(PhpSecLibCommandException::class);

        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(1))
            ->method('exec')
            ->with('command')
            ->willReturn('output');
        $sftpMock
            ->expects($this->at(2))
            ->method('getExitStatus')
            ->willReturn(1);

        $commander = new PhpSecLibCommander($sftpMock);

        $commander->exec('command');
    }

    public function testSend()
    {
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(0))
            ->method('put')
            ->with('remote', 'local', SFTP::SOURCE_LOCAL_FILE)
            ->willReturn(true);

        $commander = new PhpSecLibCommander($sftpMock);

        $commander->send('local', 'remote');
    }

    public function testSendException()
    {
        $this->expectException(PhpSecLibCommandException::class);

        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(0))
            ->method('put')
            ->with('remote', 'local', SFTP::SOURCE_LOCAL_FILE)
            ->willReturn(false);

        $commander = new PhpSecLibCommander($sftpMock);

        $commander->send('local', 'remote');
    }

    public function testRecv()
    {
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(0))
            ->method('get')
            ->with('remote', 'local')
            ->willReturn(true);

        $commander = new PhpSecLibCommander($sftpMock);

        $commander->recv('remote', 'local');
    }

    public function testRecvException()
    {
        $this->expectException(PhpSecLibCommandException::class);

        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock
            ->expects($this->at(0))
            ->method('get')
            ->with('remote', 'local')
            ->willReturn(false);

        $commander = new PhpSecLibCommander($sftpMock);

        $commander->recv('remote', 'local');
    }
}
