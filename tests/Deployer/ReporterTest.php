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
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Deployer\Reporter;
use Aes3xs\Yodler\Scenario\Action;
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\Tests\LockableStorageDummy;
use Symfony\Component\Filesystem\LockHandler;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testReset()
    {
        $storage = new LockableStorageDummy();

        $report = new Reporter($storage);
        $report->reset();

        $this->assertEquals([], $storage->getData());
    }

    public function testReportDeploy()
    {
        $storage = new LockableStorageDummy();
        $storage->setData([]);

        $scenario = new Scenario('test');
        $connection = new Connection('test');

        $report = new Reporter($storage);
        $report->reportDeploy($scenario, $connection);

        $this->assertEquals([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [],
                'failbacks'  => [],
            ],
        ], $storage->getData());
    }

    public function testReportActionRunning()
    {
        $action = $this->createMock(Action::class);

        $storage = new LockableStorageDummy();
        $storage->setData([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [],
                ],
                'failbacks'  => [],
            ],
        ]);

        $report = new Reporter($storage);
        $report->reportActionRunning($action);

        $this->assertEquals([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [
                        'state'  => Reporter::ACTION_STATE_RUNNING,
                        'start'  => date(Reporter::DATE_FORMAT),
                        'name'   => null,
                        'finish' => null,
                        'output' => null,
                    ],
                ],
                'failbacks'  => [],
            ],
        ], $storage->getData());
    }

    public function testReportActionSucceed()
    {
        $action = $this->createMock(Action::class);

        $storage = new LockableStorageDummy();
        $storage->setData([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [],
                ],
                'failbacks'  => [],
            ],
        ]);

        $report = new Reporter($storage);
        $report->reportActionSucceed($action, 'output');

        $this->assertEquals([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [
                        'state'  => Reporter::ACTION_STATE_SUCCEED,
                        'start'  => null,
                        'name'   => null,
                        'finish' => date(Reporter::DATE_FORMAT),
                        'output' => 'output',
                    ],
                ],
                'failbacks'  => [],
            ],
        ], $storage->getData());
    }

    public function testReportActionError()
    {
        $action = $this->createMock(Action::class);

        $storage = new LockableStorageDummy();
        $storage->setData([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [],
                ],
                'failbacks'  => [],
            ],
        ]);

        $report = new Reporter($storage);
        $report->reportActionError($action, new \RuntimeException('test exception'));

        $this->assertEquals([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [
                        'state'  => Reporter::ACTION_STATE_ERROR,
                        'start'  => null,
                        'name'   => null,
                        'output' => 'test exception',
                        'finish' => date(Reporter::DATE_FORMAT),
                    ],
                ],
                'failbacks'  => [],
            ],
        ], $storage->getData());
    }

    public function testReportActionSkipped()
    {
        $action = $this->createMock(Action::class);

        $storage = new LockableStorageDummy();
        $storage->setData([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [],
                ],
                'failbacks'  => [],
            ],
        ]);

        $report = new Reporter($storage);
        $report->reportActionSkipped($action);

        $this->assertEquals([
            getmypid() => [
                'scenario'   => 'test',
                'connection' => 'test',
                'actions'    => [
                    spl_object_hash($action) => [
                        'state'  => Reporter::ACTION_STATE_SKIPPED,
                        'start'  => null,
                        'name'   => null,
                        'output' => null,
                        'finish' => date(Reporter::DATE_FORMAT),
                    ],
                ],
                'failbacks'  => [],
            ],
        ], $storage->getData());
    }
}
