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
use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Heap\HeapFactory;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class HeapFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $parameterBagMock = $this->createMock(ParameterBagInterface::class);
        $parameterBagMock->method('all')->willReturn(['parameter' => 'parameterValue']);
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('getParameterBag')->willReturn($parameterBagMock);
        $containerMock->method('getServiceIds')->willReturn(['service']);
        $containerMock->method('get')->with('service')->willReturn('serviceValue');
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variablesMock->method('all')->willReturn(['variable' => 'variableValue']);
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $deployContextMock = $this->createMock(DeployContextInterface::class);
        $deployContextMock->method('getConnection')->willReturn($connectionMock);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->method('getArguments')->willReturn(['argument' => 'argumentValue']);
        $inputMock->method('getOptions')->willReturn(['option' => 'optionValue']);
        $outputMock = $this->createMock(OutputInterface::class);
        $stubVariablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->with(['parameter' => 'parameterValue'])->willReturn($stubVariablesMock);
        $variableFactoryMock->expects($this->at(1))->method('createList')->with(['service' => 'serviceValue'])->willReturn($stubVariablesMock);
        $variableFactoryMock->expects($this->at(2))->method('createList')->with([
            'deployContext' => $deployContextMock,
            'connection'    => $connectionMock,
            'input'         => $inputMock,
            'output'        => $outputMock,
        ])->willReturn($stubVariablesMock);
        $variableFactoryMock->expects($this->at(4))->method('createList')->with(['argument' => 'argumentValue'])->willReturn($stubVariablesMock);
        $variableFactoryMock->expects($this->at(3))->method('createList')->with(['option' => 'optionValue'])->willReturn($stubVariablesMock);

        $heapFactory = new HeapFactory($containerMock, $variablesMock, $variableFactoryMock);

        $heapFactory->create($deployContextMock, $inputMock, $outputMock);
    }
}
