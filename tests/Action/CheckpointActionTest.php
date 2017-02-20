<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Action;

use Aes3xs\Yodler\Action\CheckpointAction;
use Aes3xs\Yodler\Deployer\SemaphoreInterface;
use Aes3xs\Yodler\Heap\HeapInterface;

class CheckpointActionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $action = new CheckpointAction('test', $semaphoreMock);

        $this->assertEquals('test', $action->getName());
    }

    public function testSkip()
    {
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $heapMock = $this->createMock(HeapInterface::class);
        $action = new CheckpointAction('test', $semaphoreMock);

        $this->assertEquals(false, $action->skip($heapMock));
    }

    public function testExecute()
    {
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $semaphoreMock
            ->method('reportCheckpoint')
            ->with('test');
        $heapMock = $this->createMock(HeapInterface::class);
        $action = new CheckpointAction('test', $semaphoreMock);

        $action->execute($heapMock);
    }

    public function testGetDependencies()
    {
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $action = new CheckpointAction('test', $semaphoreMock);

        $this->assertEmpty($action->getDependencies());
    }
}
