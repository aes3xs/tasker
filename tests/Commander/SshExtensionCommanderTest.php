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

use Aes3xs\Yodler\Commander\SshExtensionCommander;
use Aes3xs\Yodler\Exception\SshExtensionCommandException;
use Ssh\Exec;
use Ssh\Session;
use Ssh\Sftp;

class SshExtensionCommanderTest extends \PHPUnit_Framework_TestCase
{
    public function testExec()
    {
        $sessionMock = $this->createMock(Session::class);
        $execMock = $this->createMock(Exec::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getExec')
            ->willReturn($execMock);
        $execMock
            ->expects($this->at(0))
            ->method('run')
            ->with('command')
            ->willReturn('output');

        $commander = new SshExtensionCommander($sessionMock);

        $this->assertEquals('output', $commander->exec('command'));
    }

    public function testExecException()
    {
        $this->expectException(SshExtensionCommandException::class);

        $sessionMock = $this->createMock(Session::class);
        $execMock = $this->createMock(Exec::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getExec')
            ->willReturn($execMock);
        $execMock
            ->expects($this->at(0))
            ->method('run')
            ->with('command')
            ->willThrowException(new SshExtensionCommandException());

        $commander = new SshExtensionCommander($sessionMock);

        $commander->exec('command');
    }

    public function testSend()
    {
        $sessionMock = $this->createMock(Session::class);
        $sftpMock = $this->createMock(Sftp::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getSftp')
            ->willReturn($sftpMock);
        $sftpMock
            ->expects($this->at(0))
            ->method('send')
            ->with('local', 'remote')
            ->willReturn(true);

        $commander = new SshExtensionCommander($sessionMock);

        $commander->send('local', 'remote');
    }

    public function testSendException()
    {
        $this->expectException(SshExtensionCommandException::class);

        $sessionMock = $this->createMock(Session::class);
        $sftpMock = $this->createMock(Sftp::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getSftp')
            ->willReturn($sftpMock);
        $sftpMock
            ->expects($this->at(0))
            ->method('send')
            ->with('local', 'remote')
            ->willReturn(false);

        $commander = new SshExtensionCommander($sessionMock);

        $commander->send('local', 'remote');
    }

    public function testRecv()
    {
        $sessionMock = $this->createMock(Session::class);
        $sftpMock = $this->createMock(Sftp::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getSftp')
            ->willReturn($sftpMock);
        $sftpMock
            ->expects($this->at(0))
            ->method('receive')
            ->with('remote', 'local')
            ->willReturn(true);

        $commander = new SshExtensionCommander($sessionMock);

        $commander->recv('remote', 'local');
    }

    public function testRecvException()
    {
        $this->expectException(SshExtensionCommandException::class);

        $sessionMock = $this->createMock(Session::class);
        $sftpMock = $this->createMock(Sftp::class);
        $sessionMock
            ->expects($this->at(0))
            ->method('getSftp')
            ->willReturn($sftpMock);
        $sftpMock
            ->expects($this->at(0))
            ->method('receive')
            ->with('remote', 'local')
            ->willReturn(false);

        $commander = new SshExtensionCommander($sessionMock);

        $commander->recv('remote', 'local');
    }
}
