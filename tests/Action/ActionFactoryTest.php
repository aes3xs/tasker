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

use Aes3xs\Yodler\Action\ActionFactory;
use Aes3xs\Yodler\Action\ActionList;
use Aes3xs\Yodler\Action\CheckpointAction;
use Aes3xs\Yodler\Action\MessageAction;
use Aes3xs\Yodler\Action\TaskAction;
use Aes3xs\Yodler\Deployer\SemaphoreInterface;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Psr\Log\LoggerInterface;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTaskAction()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $action = $factory->create(['task' => 'test', 'condition' => 'condition']);
        $expectedAction = new TaskAction('test', 'condition');

        $this->assertEquals($expectedAction, $action);
    }

    public function testCreateCheckpointAction()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $action = $factory->create(['checkpoint' => 'test']);
        $expectedAction = new CheckpointAction('test', $semaphoreMock);

        $this->assertEquals($expectedAction, $action);
    }

    public function testCreateMessageAction()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $action = $factory->create(['message' => 'test', 'level' => 'info']);
        $expectedAction = new MessageAction('test', 'info', $loggerMock);

        $this->assertEquals($expectedAction, $action);
    }

    public function testCreateMessageActionDefaultLevel()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $action = $factory->create(['message' => 'test']);
        $expectedAction = new MessageAction('test', 'notice', $loggerMock);

        $this->assertEquals($expectedAction, $action);
    }

    public function testCreateNotRecognizedException()
    {
        $this->expectException(RuntimeException::class);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $factory->create([]);
    }

    public function testCreateList()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $list = $factory->createList();

        $this->assertInstanceOf(ActionList::class, $list);
        $this->assertEmpty($list->all());
    }

    public function testCreateListFromConfiguration()
    {
        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $configuration = [
            ['task' => 'test', 'condition' => 'condition'],
            ['message' => 'test', 'level' => 'notice'],
            ['checkpoint' => 'test'],
        ];

        $list = $factory->createListFromConfiguration($configuration);
        $expectedTaskAction = new TaskAction('test', 'condition');
        $expectedMessageAction = new MessageAction('test', 'notice', $loggerMock);
        $expectedCheckpointAction = new CheckpointAction('test', $semaphoreMock);

        $this->assertInstanceOf(ActionList::class, $list);
        $this->assertCount(3, $list->all());
        $this->assertEquals($expectedTaskAction, $list->all()[0]);
        $this->assertEquals($expectedMessageAction, $list->all()[1]);
        $this->assertEquals($expectedCheckpointAction, $list->all()[2]);
    }

    public function testBadConfiguration()
    {
        $this->expectException(\Exception::class);

        $variablesMock = $this->createMock(VariableListInterface::class);
        $semaphoreMock = $this->createMock(SemaphoreInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $factory = new ActionFactory($variablesMock, $semaphoreMock, $loggerMock);
        $configuration = [
            'test' => [
                'wrong_parameter' => 'value',
            ],
        ];
        $factory->createListFromConfiguration($configuration);
    }
}
