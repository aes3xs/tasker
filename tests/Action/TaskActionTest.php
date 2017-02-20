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

use Aes3xs\Yodler\Action\TaskAction;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Heap\HeapInterface;

class TaskActionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $action = new TaskAction('test', 'condition');

        $this->assertEquals('Test', $action->getName());
    }

    public function testSkipWithConditionTrue()
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock
            ->method('resolveExpression')
            ->with('condition')
            ->willReturn(true);
        $action = new TaskAction('test', 'condition');

        $this->assertFalse($action->skip($heapMock));
    }

    public function testSkipWithConditionFalse()
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock
            ->method('resolveExpression')
            ->with('condition')
            ->willReturn(false);
        $action = new TaskAction('test', 'condition');

        $this->assertTrue($action->skip($heapMock));
    }

    public function testSkipWithoutCondition()
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $action = new TaskAction('test', false);

        $this->assertFalse($action->skip($heapMock));
    }

    public function testExecute()
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock
            ->method('get')
            ->with('test')
            ->willReturn(function () {
                return 'value';
            });
        $action = new TaskAction('test', 'condition');

        $this->assertEquals('value', $action->execute($heapMock));
    }

    public function testExecuteNotCallbackException()
    {
        $this->expectException(RuntimeException::class);

        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock
            ->method('get')
            ->with('test')
            ->willReturn('notCallback');
        $action = new TaskAction('test', 'condition');

        $action->execute($heapMock);
    }

    public function testGetDependencies()
    {
        $action = new TaskAction('test', 'condition');

        $this->assertEquals(['test'], $action->getDependencies());
    }
}
