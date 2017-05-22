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

use Aes3xs\Yodler\Deployer\Semaphore;
use Aes3xs\Yodler\Exception\ErrorInterruptException;
use Aes3xs\Yodler\Exception\TimeoutInterruptException;
use Aes3xs\Yodler\Tests\LockableStorageDummy;

class SemaphoreTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $storage = new LockableStorageDummy();

        $semaphore = new Semaphore($storage);
        $semaphore->reset();

        $this->assertEquals([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [],
        ], $storage->getData());
    }

    public function testAddProcess()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->addProcess(getmypid());

        $this->assertEquals([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [getmypid() => []],
        ], $storage->getData());
    }

    public function testReportReady()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => [getmypid() => []],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportReady();

        $this->assertEquals([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => [getmypid() => [Semaphore::CHECKPOINT_INIT]],
        ], $storage->getData());
    }

    public function testReportReadyWaitOnce()
    {
        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_SUSPENDED,
                'checkpoints' => [],
            ]);

        $storage
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_SUSPENDED,
                'checkpoints' => [getmypid() => [], 'wqe' => null],
            ]);

        $storage
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => []],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportReady();
    }

    public function testReportReadyWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [getmypid() => []],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->setTimeout(0);
        $semaphore->reportReady();
    }

    public function testReportCheckpoint()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => [getmypid() => []],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportCheckpoint('test');

        $this->assertEquals([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => [getmypid() => ['test']],
        ], $storage->getData());
    }

    public function testReportCheckpointWaitOnce()
    {
        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => [], 'another_process' => []],
            ]);

        $storage
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => ['test'], 'another_process' => []],
            ]);

        $storage
            ->expects($this->at(2))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => ['test'], 'another_process' => ['test']],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportCheckpointWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => [], 'another_process' => []],
            ]);

        $storage
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => ['test'], 'another_process' => []],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->setTimeout(0);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportCheckpointErrorInterrupt()
    {
        $this->expectException(ErrorInterruptException::class);

        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => [], 'another_process' => []],
            ]);

        $storage
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_RUNNING,
                'checkpoints' => [getmypid() => ['test'], 'another_process' => [Semaphore::CHECKPOINT_ERROR]],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportCheckpoint('test');
    }

    public function testReportError()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [getmypid() => []],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->reportError();

        $this->assertEquals([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => [getmypid() => [Semaphore::CHECKPOINT_ERROR]],
        ], $storage->getData());
    }

    public function testRun()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([
            'state'       => Semaphore::STATE_SUSPENDED,
            'checkpoints' => ['process' => [Semaphore::CHECKPOINT_INIT]],
        ]);

        $semaphore = new Semaphore($storage);
        $semaphore->run();

        $this->assertEquals([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => ['process' => [Semaphore::CHECKPOINT_INIT]],
        ], $storage->getData());
    }

    public function testRunWaitOnce()
    {
        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_SUSPENDED,
                'checkpoints' => ['process' => []],
            ]);

        $storage
            ->expects($this->at(1))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_SUSPENDED,
                'checkpoints' => ['process' => [Semaphore::CHECKPOINT_INIT]],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->run();

        $this->assertEquals([
            'state'       => Semaphore::STATE_RUNNING,
            'checkpoints' => ['process' => [Semaphore::CHECKPOINT_INIT]],
        ], $storage->getData());
    }

    public function testRunWaitTimeout()
    {
        $this->expectException(TimeoutInterruptException::class);

        $storage = $this->getMockBuilder(LockableStorageDummy::class)->setMethods(['read'])->getMock();

        $storage
            ->expects($this->at(0))
            ->method('read')
            ->willReturn([
                'state'       => Semaphore::STATE_SUSPENDED,
                'checkpoints' => ['process' => []],
            ]);

        $semaphore = new Semaphore($storage);
        $semaphore->setTimeout(0);
        $semaphore->run();
    }
}
