<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Variable;

use Aes3xs\Yodler\Exception\ClassNotFoundException;
use Aes3xs\Yodler\Exception\FileNotFoundException;
use Aes3xs\Yodler\Variable\VariableFactory;
use Aes3xs\Yodler\Variable\VariableInterface;

/**
 * Contains tests with internal file require, run in isolated process.
 *
 * @runTestsInSeparateProcesses
 */
class VariableFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateList()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createList(['test' => 'value']);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testCreateListFromConfigurationByFileAndClass()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testCreateListFromConfigurationByClass()
    {
        $variableFactory = new VariableFactory();

        require __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php';

        $list = $variableFactory->createListFromConfiguration([
            '\VariableSourceFixture'
        ]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testFileNotFoundException()
    {
        $this->expectException(FileNotFoundException::class);

        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/NotExistingFile.php' => '\VariableSourceFixture'
        ]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testCreateListFromConfigurationWithInvalidClass()
    {
        $this->expectException(ClassNotFoundException::class);

        $variableFactory = new VariableFactory();

        require __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php';

        $list = $variableFactory->createListFromConfiguration([
            '\NotExistingClass'
        ]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testShortNameGeneration()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertTrue($list->has('doTest2'));
        $this->assertTrue($list->has('test2'));
        $this->assertEquals('value2', $list->get('test2')->getValue());

        $this->assertTrue($list->has('runTest3'));
        $this->assertTrue($list->has('test3'));
        $this->assertEquals('value3', $list->get('test3')->getValue());

        $this->assertTrue($list->has('getTest4'));
        $this->assertTrue($list->has('test4'));
        $this->assertEquals('value4', $list->get('test4')->getValue());

        $this->assertTrue($list->has('setTest5'));
        $this->assertTrue($list->has('test5'));
        $this->assertEquals('value5', $list->get('test5')->getValue());
    }

    public function testEvaluated()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('evaluated'));
        $this->assertEquals('valueEvaluated', $list->get('evaluated')->getValue());

        $this->assertInstanceOf(VariableInterface::class, $list->get('notEvaluated'));
        $this->assertTrue(is_callable($list->get('notEvaluated')->getValue()));
        $callback = $list->get('notEvaluated')->getValue();
        $this->assertEquals('valueNotEvaluated', call_user_func($callback, 'argument'));
    }
}
