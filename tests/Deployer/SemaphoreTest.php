<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Deployer;

use Aes3xs\Yodler\Common\SharedMemoryHandler;
use Aes3xs\Yodler\Deployer\Semaphore;
use Aes3xs\Yodler\Exception\ErrorInterruptException;
use Aes3xs\Yodler\Exception\TimeoutInterruptException;
use Symfony\Component\Filesystem\LockHandler;

class SemaphoreTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('dump')
            ->with([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->reset();
    }

    public function testReportReadyWaitOnce()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [],
                'checkpoints'    => [1 => []],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->reportReady(1);
    }

    public function testReportReadyWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $sharedMemoryHandlerMock
            ->expects($this->any())
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [1 => []],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->setTimeout(0);
        $semaphore->reportReady(1);
    }

    public function testReportCheckpointWaitOnce()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        // Called from reportReady()
        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => [], 2 => []],
            ]);

        // Called from reportCheckpoint()
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => ['test'], 2 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(4))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => ['test'], 2 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(5))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => ['test'], 2 => ['test']],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->reportReady(1);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportCheckpointWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        // Called from reportReady()
        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => [], 2 => []],
            ]);

        // Called from reportCheckpoint()
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => ['test'], 2 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(4))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => ['test'], 2 => []],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->setTimeout(0);
        $semaphore->reportReady(1);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportCheckpointErrorInterrupt()
    {
        $this->expectException(ErrorInterruptException::class);

        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        // Called from reportReady()
        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => [], 2 => []],
            ]);

        // Called from reportCheckpoint()
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => [], 2 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(4))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1, 2],
                'checkpoints'    => [1 => [], 2 => ['@error']],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->reportReady(1);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportError()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        // Called from reportReady()
        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1],
                'checkpoints'    => [1 => []],
            ]);

        // Called from reportCheckpoint()
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1],
                'checkpoints'    => [1 => ['@error']],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->reportReady(1);
        $semaphore->reportError();
    }

    public function testRunWaitOnce()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [1 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [1 => []],
            ]);
        $sharedMemoryHandlerMock
            ->expects($this->at(3))
            ->method('dump')
            ->willReturn([
                'state'          => Semaphore::STATE_RUNNING,
                'concurrent_ids' => [1],
                'checkpoints'    => [1 => []],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->run([1]);
    }

    public function testRunWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $sharedMemoryHandlerMock
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'          => Semaphore::STATE_SUSPENDED,
                'concurrent_ids' => [],
                'checkpoints'    => [],
            ]);

        $semaphore = new Semaphore($lockHandlerMock, $sharedMemoryHandlerMock);
        $semaphore->setTimeout(0);
        $semaphore->run([1]);
    }
}
