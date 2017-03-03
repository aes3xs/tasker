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

use Aes3xs\Yodler\Deployer\Executor;
use Aes3xs\Yodler\Deployer\ReportInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Scenario\ActionInterface;
use Aes3xs\Yodler\Scenario\ActionListInterface;
use Psr\Log\LoggerInterface;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $reportMock = $this->createMock(ReportInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $executor = new Executor($heapMock, $reportMock, $loggerMock);

        $actionMock = $this->createMock(ActionInterface::class);
        $actionMock->expects($this->at(0))->method('getName')->willReturn('test');
        $actionMock->expects($this->at(1))->method('getCondition')->willReturn(null);
        $actionMock->expects($this->at(2))->method('getCallback')->willReturn(function () {});
        $actionsMock = $this->createMock(ActionListInterface::class);
        $actionsMock->method('all')->willReturn([$actionMock]);

        $executor->execute($actionsMock);
    }

    public function testExecuteError()
    {
        $this->expectException(\RuntimeException::class);

        $heapMock = $this->createMock(HeapInterface::class);
        $reportMock = $this->createMock(ReportInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $executor = new Executor($heapMock, $reportMock, $loggerMock);

        $actionMock = $this->createMock(ActionInterface::class);
        $actionMock->expects($this->at(0))->method('getName')->willThrowException(new \RuntimeException());
        $actionsMock = $this->createMock(ActionListInterface::class);
        $actionsMock->method('all')->willReturn([$actionMock]);

        $executor->execute($actionsMock);
    }
}
