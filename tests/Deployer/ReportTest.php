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

use Aes3xs\Yodler\Action\ActionInterface;
use Aes3xs\Yodler\Action\ActionListInterface;
use Aes3xs\Yodler\Common\SharedMemoryHandler;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Deployer\Report;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\Filesystem\LockHandler;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('dump')->with(['deploys' => [], 'actions' => []]);

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->reset();
    }

    public function testNotInitialized()
    {
        $this->expectException(RuntimeException::class);

        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->reportActionRunning($this->createMock(ActionInterface::class));
    }

    public function testReportDeploy()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);

        $actionsMock = $this->createMock(ActionListInterface::class);
        $actionsMock->method('all')->willReturn([]);
        $failbackActionsMock = $this->createMock(ActionListInterface::class);
        $failbackActionsMock->method('all')->willReturn([]);
        $deployMock = $this->createMock(DeployInterface::class);
        $deployMock->method('getName')->willReturn('deploy');
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $scenarioMock->method('getName')->willReturn('scenario');
        $scenarioMock->method('getActions')->willReturn($actionsMock);
        $scenarioMock->method('getFailbackActions')->willReturn($failbackActionsMock);
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('getName')->willReturn('connection');
        $deployContextMock = $this->createMock(DeployContextInterface::class);
        $deployContextMock->method('getDeploy')->willReturn($deployMock);
        $deployContextMock->method('getScenario')->willReturn($scenarioMock);
        $deployContextMock->method('getConnection')->willReturn($connectionMock);

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->initialize(1);
        $report->reportDeploy($deployContextMock);
    }

    public function testReportActionRunning()
    {
        $actionMock = $this->createMock(ActionInterface::class);
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);
        $sharedMemoryHandlerMock->expects($this->at(1))->method('dump')->with($this->equalTo([
            'deploys' => [],
            'actions' => [
                1 => [
                    spl_object_hash($actionMock) => [
                        'state' => Report::ACTION_STATE_RUNNING,
                        'start' => date(Report::DATE_FORMAT),
                    ],
                ],
            ],
        ], 5)); // 5 sec delta

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->initialize(1);
        $report->reportActionRunning($actionMock);
    }

    public function testReportActionSucceed()
    {
        $actionMock = $this->createMock(ActionInterface::class);
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);
        $sharedMemoryHandlerMock->expects($this->at(1))->method('dump')->with($this->equalTo([
            'deploys' => [],
            'actions' => [
                1 => [
                    spl_object_hash($actionMock) => [
                        'state'  => Report::ACTION_STATE_SUCCEED,
                        'output' => 'output',
                        'finish' => date(Report::DATE_FORMAT),
                    ],
                ],
            ],
        ], 5)); // 5 sec delta

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->initialize(1);
        $report->reportActionSucceed($actionMock, 'output');
    }

    public function testReportActionError()
    {
        $actionMock = $this->createMock(ActionInterface::class);
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);
        $sharedMemoryHandlerMock->expects($this->at(1))->method('dump')->with($this->equalTo([
            'deploys' => [],
            'actions' => [
                1 => [
                    spl_object_hash($actionMock) => [
                        'state'  => Report::ACTION_STATE_ERROR,
                        'output' => 'test exception',
                        'finish' => date(Report::DATE_FORMAT),
                    ],
                ],
            ],
        ], 5)); // 5 sec delta

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->initialize(1);
        $report->reportActionError($actionMock, new \RuntimeException('test exception'));
    }

    public function testReportActionSkipped()
    {
        $actionMock = $this->createMock(ActionInterface::class);
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);
        $sharedMemoryHandlerMock->expects($this->at(1))->method('dump')->with($this->equalTo([
            'deploys' => [],
            'actions' => [
                1 => [
                    spl_object_hash($actionMock) => [
                        'state'  => Report::ACTION_STATE_SKIPPED,
                        'finish' => date(Report::DATE_FORMAT),
                    ],
                ],
            ],
        ], 5)); // 5 sec delta

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $report->initialize(1);
        $report->reportActionSkipped($actionMock);
    }

    public function testGetRawData()
    {
        $lockHandlerMock = $this->createMock(LockHandler::class);
        $sharedMemoryHandlerMock = $this->createMock(SharedMemoryHandler::class);
        $sharedMemoryHandlerMock->expects($this->at(0))->method('read')->willReturn(['deploys' => [], 'actions' => []]);

        $report = new Report($lockHandlerMock, $sharedMemoryHandlerMock);
        $this->assertEquals(['deploys' => [], 'actions' => []], $report->getRawData());
    }
}
