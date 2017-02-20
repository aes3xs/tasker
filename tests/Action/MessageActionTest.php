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

use Aes3xs\Yodler\Action\MessageAction;
use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;

class MessageActionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $action = new MessageAction('test', 'notice', $loggerMock);

        $this->assertEquals('Notice', $action->getName());
    }

    public function testSkip()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $heapMock = $this->createMock(HeapInterface::class);
        $action = new MessageAction('test', 'notice', $loggerMock);

        $this->assertEquals(false, $action->skip($heapMock));
    }

    public function testExecute()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->method('log')
            ->with('notice', 'resolvedString', []);
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock
            ->method('resolveString')
            ->with('test')
            ->willReturn('resolvedString');
        $action = new MessageAction('test', 'notice', $loggerMock);

        $action->execute($heapMock);
    }

    public function testGetDependencies()
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $action = new MessageAction('test', 'notice', $loggerMock);

        $this->assertEmpty($action->getDependencies());
    }
}
