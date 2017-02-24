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

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Event\DeployEvent;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Heap\HeapFactoryInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Aes3xs\Yodler\Heap\LazyHeapProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
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
        $commandMock = $this->createMock(Command::class);
        $commandEvent = new ConsoleCommandEvent($commandMock, $inputMock, $outputMock);
        $deployContext = $this->createMock(DeployContextInterface::class);
        $deployEvent = new DeployEvent($deployContext);
        $heapFactory = $this->createMock(HeapFactoryInterface::class);
        $heapFactory->method('create')->with($deployContext)->willReturn($heapMock);

        $heap = new LazyHeapProxy($heapFactory);

        $heap->onCommand($commandEvent);
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

    public function testResolve()
    {
        $this->assertLazyHeapProxyMethod('resolve', 'test', 'value');
    }

    public function testResolveString()
    {
        $this->assertLazyHeapProxyMethod('resolveString', 'test', 'value');
    }

    public function testResolveExpression()
    {
        $this->assertLazyHeapProxyMethod('resolveExpression', 'test', 'value');
    }

    public function testGetDependencies()
    {
        $this->assertLazyHeapProxyMethod('getDependencies', 'test', ['value']);
    }

    public function testInitializeException()
    {
        $this->expectException(RuntimeException::class);

        $heapFactory = $this->createMock(HeapFactoryInterface::class);

        $heap = new LazyHeapProxy($heapFactory);

        $heap->has('test');
    }
}
