<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Scenario;

use Aes3xs\Yodler\Action\ActionFactoryInterface;
use Aes3xs\Yodler\Action\ActionListInterface;
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\Scenario\ScenarioFactory;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;

class ScenarioFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateListFromConfiguration()
    {
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionList = $this->createMock(ActionListInterface::class);
        $failbackActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([['task' => 'task1']])->willReturn($actionList);
        $actionFactoryMock->expects($this->at(1))->method('createListFromConfiguration')->with([['task' => 'task2']])->willReturn($failbackActionList);
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with(['test' => 'value'])->willReturn($variablesMock);

        $scenarioFactory = new ScenarioFactory($actionFactoryMock, $variableFactoryMock);

        $configuration = [
            'test' => [
                'actions' => [
                    ['task' => 'task1'],
                ],
                'failback' => [
                    ['task' => 'task2'],
                ],
                'variables'  => [
                    'test' => 'value',
                ],
            ],
        ];

        $list = $scenarioFactory->createListFromConfiguration($configuration);
        $this->assertCount(1, $list->all());

        $scenario = $list->get('test');
        $expectedScenario = new Scenario('test', $actionList, $failbackActionList, $variablesMock);

        $this->assertEquals($expectedScenario, $scenario);
    }

    public function testConfigurationDefaults()
    {
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionList = $this->createMock(ActionListInterface::class);
        $failbackActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([['task' => 'task']])->willReturn($actionList);
        $actionFactoryMock->expects($this->at(1))->method('createListFromConfiguration')->with([])->willReturn($failbackActionList);
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $scenarioFactory = new ScenarioFactory($actionFactoryMock, $variableFactoryMock);

        $configuration = [
            'test' => [
                'actions' => [['task' => 'task']],
            ],
        ];

        $list = $scenarioFactory->createListFromConfiguration($configuration);

        $scenario = $list->get('test');
        $expectedScenario = new Scenario('test', $actionList, $failbackActionList, $variablesMock);

        $this->assertEquals($expectedScenario, $scenario);
    }

    public function testMultiple()
    {
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionList1 = $this->createMock(ActionListInterface::class);
        $failbackActionList1 = $this->createMock(ActionListInterface::class);
        $actionList2 = $this->createMock(ActionListInterface::class);
        $failbackActionList2 = $this->createMock(ActionListInterface::class);
        $actionList3 = $this->createMock(ActionListInterface::class);
        $failbackActionList3 = $this->createMock(ActionListInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([['task' => 'task1']])->willReturn($actionList1);
        $actionFactoryMock->expects($this->at(1))->method('createListFromConfiguration')->with([])->willReturn($failbackActionList1);
        $actionFactoryMock->expects($this->at(2))->method('createListFromConfiguration')->with([['task' => 'task2']])->willReturn($actionList2);
        $actionFactoryMock->expects($this->at(3))->method('createListFromConfiguration')->with([])->willReturn($failbackActionList2);
        $actionFactoryMock->expects($this->at(4))->method('createListFromConfiguration')->with([['task' => 'task3']])->willReturn($actionList3);
        $actionFactoryMock->expects($this->at(5))->method('createListFromConfiguration')->with([])->willReturn($failbackActionList3);
        $variablesMock1 = $this->createMock(VariableListInterface::class);
        $variablesMock2 = $this->createMock(VariableListInterface::class);
        $variablesMock3 = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->with([])->willReturn($variablesMock1);
        $variableFactoryMock->expects($this->at(1))->method('createList')->with([])->willReturn($variablesMock2);
        $variableFactoryMock->expects($this->at(2))->method('createList')->with([])->willReturn($variablesMock3);

        $scenarioFactory = new ScenarioFactory($actionFactoryMock, $variableFactoryMock);

        $configuration = [
            'test1' => [
                'actions' => [['task' => 'task1']],
            ],
            'test2' => [
                'actions' => [['task' => 'task2']],
            ],
            'test3' => [
                'actions' => [['task' => 'task3']],
            ],
        ];

        $list = $scenarioFactory->createListFromConfiguration($configuration);

        $this->assertCount(3, $list->all());

        $expectedScenario1 = new Scenario('test1', $actionList1, $failbackActionList1, $variablesMock1);
        $expectedScenario2 = new Scenario('test2', $actionList2, $failbackActionList2, $variablesMock2);
        $expectedScenario3 = new Scenario('test3', $actionList3, $failbackActionList3, $variablesMock3);

        $this->assertEquals($expectedScenario1, $list->get('test1'));
        $this->assertEquals($expectedScenario2, $list->get('test2'));
        $this->assertEquals($expectedScenario3, $list->get('test3'));
    }

    public function testBadConfiguration()
    {
        $this->expectException(\Exception::class);

        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);

        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];

        $scenarioFactory = new ScenarioFactory($actionFactoryMock, $variableFactoryMock);

        $scenarioFactory->createListFromConfiguration($configuration);
    }

    public function testCreateStubConnection()
    {
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionList = $this->createMock(ActionListInterface::class);
        $failbackActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createList')->willReturn($actionList);
        $actionFactoryMock->expects($this->at(1))->method('createList')->willReturn($failbackActionList);
        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->method('createList')->with([])->willReturn($variablesMock);

        $scenarioFactory = new ScenarioFactory($actionFactoryMock, $variableFactoryMock);

        $scenario = $scenarioFactory->createStubScenario();
        $expectedScenario = new Scenario(null, $actionList, $failbackActionList, $variablesMock);

        $this->assertEquals($expectedScenario, $scenario);
    }
}
