<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Deploy;

use Aes3xs\Yodler\Action\ActionFactoryInterface;
use Aes3xs\Yodler\Action\ActionListInterface;
use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Connection\ConnectionListInterface;
use Aes3xs\Yodler\Deploy\Build;
use Aes3xs\Yodler\Deploy\BuildList;
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Deploy\DeployFactory;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioListInterface;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;

class DeployFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateListFromConfiguration()
    {
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $scenariosMock = $this->createMock(ScenarioListInterface::class);
        $scenariosMock->expects($this->at(0))->method('get')->with('scenario')->willReturn($scenarioMock);

        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);
        $connectionsMock->expects($this->at(0))->method('get')->with('connection')->willReturn($connectionMock);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->with(['test' => 'value'])->willReturn($variablesMock);

        $doneActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([['task' => 'test']])->willReturn($doneActionList);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $configuration = [
            'test' => [
                'builds' => [
                    ['scenario' => 'scenario', 'connection' => 'connection'],
                ],
                'done' => [
                    ['task' => 'test'],
                ],
                'variables'  => [
                    'test' => 'value',
                ],
            ],
        ];

        $list = $deployFactory->createListFromConfiguration($configuration);
        $this->assertCount(1, $list->all());

        $deploy = $list->get('test');
        $expectedBuilds = new BuildList();
        $expectedBuilds->add(new Build($scenarioMock, $connectionMock));
        $expectedDeploy = new Deploy('test', $expectedBuilds, $doneActionList, $variablesMock);

        $this->assertEquals($expectedDeploy, $deploy);
    }

    public function testConfigurationDefaults()
    {
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $scenariosMock = $this->createMock(ScenarioListInterface::class);
        $scenariosMock->expects($this->at(0))->method('get')->with('scenario')->willReturn($scenarioMock);

        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);
        $connectionsMock->expects($this->at(0))->method('get')->with('connection')->willReturn($connectionMock);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->with([])->willReturn($variablesMock);

        $doneActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([])->willReturn($doneActionList);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $configuration = [
            'test' => [
                'builds' => [
                    ['scenario' => 'scenario', 'connection' => 'connection'],
                ],
            ],
        ];

        $list = $deployFactory->createListFromConfiguration($configuration);

        $deploy = $list->get('test');
        $expectedBuilds = new BuildList();
        $expectedBuilds->add(new Build($scenarioMock, $connectionMock));
        $expectedDeploy = new Deploy('test', $expectedBuilds, $doneActionList, $variablesMock);

        $this->assertEquals($expectedDeploy, $deploy);
    }

    public function testMultiple()
    {
        $scenarioMock1 = $this->createMock(ScenarioInterface::class);
        $scenarioMock2 = $this->createMock(ScenarioInterface::class);
        $scenarioMock3 = $this->createMock(ScenarioInterface::class);
        $scenariosMock = $this->createMock(ScenarioListInterface::class);
        $scenariosMock->expects($this->at(0))->method('get')->with('scenario1')->willReturn($scenarioMock1);
        $scenariosMock->expects($this->at(1))->method('get')->with('scenario2')->willReturn($scenarioMock2);
        $scenariosMock->expects($this->at(2))->method('get')->with('scenario3')->willReturn($scenarioMock3);

        $connectionMock1 = $this->createMock(ConnectionInterface::class);
        $connectionMock2 = $this->createMock(ConnectionInterface::class);
        $connectionMock3 = $this->createMock(ConnectionInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);
        $connectionsMock->expects($this->at(0))->method('get')->with('connection1')->willReturn($connectionMock1);
        $connectionsMock->expects($this->at(1))->method('get')->with('connection2')->willReturn($connectionMock2);
        $connectionsMock->expects($this->at(2))->method('get')->with('connection3')->willReturn($connectionMock3);

        $variablesMock1 = $this->createMock(VariableListInterface::class);
        $variablesMock2 = $this->createMock(VariableListInterface::class);
        $variablesMock3 = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->with([])->willReturn($variablesMock1);
        $variableFactoryMock->expects($this->at(1))->method('createList')->with([])->willReturn($variablesMock2);
        $variableFactoryMock->expects($this->at(2))->method('createList')->with([])->willReturn($variablesMock3);

        $doneActionList1 = $this->createMock(ActionListInterface::class);
        $doneActionList2 = $this->createMock(ActionListInterface::class);
        $doneActionList3 = $this->createMock(ActionListInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createListFromConfiguration')->with([])->willReturn($doneActionList1);
        $actionFactoryMock->expects($this->at(1))->method('createListFromConfiguration')->with([])->willReturn($doneActionList2);
        $actionFactoryMock->expects($this->at(2))->method('createListFromConfiguration')->with([])->willReturn($doneActionList3);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $configuration = [
            'test1' => [
                'builds' => [
                    ['scenario' => 'scenario1', 'connection' => 'connection1'],
                ],
            ],
            'test2' => [
                'builds' => [
                    ['scenario' => 'scenario2', 'connection' => 'connection2'],
                ],
            ],
            'test3' => [
                'builds' => [
                    ['scenario' => 'scenario3', 'connection' => 'connection3'],
                ],
            ],
        ];

        $list = $deployFactory->createListFromConfiguration($configuration);

        $this->assertCount(3, $list->all());

        $deploy1 = $list->get('test1');
        $expectedBuilds1 = new BuildList();
        $expectedBuilds1->add(new Build($scenarioMock1, $connectionMock1));
        $expectedDeploy1 = new Deploy('test1', $expectedBuilds1, $doneActionList1, $variablesMock1);

        $deploy2 = $list->get('test2');
        $expectedBuilds2 = new BuildList();
        $expectedBuilds2->add(new Build($scenarioMock2, $connectionMock2));
        $expectedDeploy2 = new Deploy('test2', $expectedBuilds2, $doneActionList2, $variablesMock2);

        $deploy3 = $list->get('test3');
        $expectedBuilds3 = new BuildList();
        $expectedBuilds3->add(new Build($scenarioMock3, $connectionMock3));
        $expectedDeploy3 = new Deploy('test3', $expectedBuilds3, $doneActionList3, $variablesMock3);

        $this->assertEquals($expectedDeploy1, $deploy1);
        $this->assertEquals($expectedDeploy2, $deploy2);
        $this->assertEquals($expectedDeploy3, $deploy3);
    }

    public function testBadConfiguration()
    {
        $this->expectException(\Exception::class);

        $scenariosMock = $this->createMock(ScenarioListInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];

        $deployFactory->createListFromConfiguration($configuration);
    }

    public function testCreateStubConnection()
    {
        $scenariosMock = $this->createMock(ScenarioListInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->willReturn($variablesMock);

        $doneActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createList')->willReturn($doneActionList);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $deploy = $deployFactory->createStubDeploy();
        $expectedBuilds = new BuildList();
        $expectedDeploy = new Deploy(null, $expectedBuilds, $doneActionList, $variablesMock);

        $this->assertEquals($expectedDeploy, $deploy);
    }

    public function testCreateFromScenarioAndConnection()
    {
        $scenarioMock = $this->createMock(ScenarioInterface::class);
        $scenariosMock = $this->createMock(ScenarioListInterface::class);

        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionsMock = $this->createMock(ConnectionListInterface::class);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $variableFactoryMock = $this->createMock(VariableFactoryInterface::class);
        $variableFactoryMock->expects($this->at(0))->method('createList')->willReturn($variablesMock);

        $doneActionList = $this->createMock(ActionListInterface::class);
        $actionFactoryMock = $this->createMock(ActionFactoryInterface::class);
        $actionFactoryMock->expects($this->at(0))->method('createList')->willReturn($doneActionList);

        $deployFactory = new DeployFactory($scenariosMock, $connectionsMock, $variableFactoryMock, $actionFactoryMock);

        $deploy = $deployFactory->createFromScenarioAndConnection($scenarioMock, $connectionMock);

        $expectedBuilds = new BuildList();
        $expectedBuilds->add(new Build($scenarioMock, $connectionMock));
        $expectedDeploy = new Deploy(null, $expectedBuilds, $doneActionList, $variablesMock);

        $this->assertEquals($expectedDeploy, $deploy);
    }
}
