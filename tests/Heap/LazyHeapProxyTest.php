<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Heap;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Console\Application;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Heap\HeapFactoryInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Heap\LazyHeapProxy;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LazyHeapProxyTest extends \PHPUnit_Framework_TestCase
{
    protected function assertLazyHeapProxyMethod($method, $name, $value)
    {
        $heapMock = $this->createMock(HeapInterface::class);
        $heapMock->method($method)->with($name)->willReturn($value);
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);
        $applicationMock = $this->createMock(Application::class);
        $consoleRunEvent = new ConsoleRunEvent($applicationMock, $inputMock, $outputMock);
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $deployEvent = new DeployEvent($scenarioMock, $connectionMock);
        $heapFactory = $this->createMock(HeapFactoryInterface::class);
        $heapFactory->expects($this->at(0))->method('create')->with($scenarioMock, $connectionMock, $inputMock, $outputMock)->willReturn($heapMock);

        $heap = new LazyHeapProxy($heapFactory);

        $heap->onConsoleRun($consoleRunEvent);
        $heap->onDeploy($deployEvent);

        $this->assertSame($value, $heap->{$method}($name));
    }

    public function testHas()
    {
        $this->assertLazyHeapProxyMethod('has', 'test', true);
    }

    public function testGet()
    {
        $this->assertLazyHeapProxyMethod('get', 'test', 'value');
    }

    public function testResolveString()
    {
        $this->assertLazyHeapProxyMethod('resolveString', 'test', 'value');
    }

    public function testResolveExpression()
    {
        $this->assertLazyHeapProxyMethod('resolveExpression', 'test', 'value');
    }

    public function testInitializeException()
    {
        $this->expectException(RuntimeException::class);

        $heapFactory = $this->createMock(HeapFactoryInterface::class);

        $heap = new LazyHeapProxy($heapFactory);

        $heap->has('test');
    }
}
