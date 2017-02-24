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

        $this->assertEquals('value', $list->get('test'));
    }

    public function testCreateListFromConfigurationByFileAndClass()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testCreateListFromConfigurationByClass()
    {
        $variableFactory = new VariableFactory();

        require __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php';

        $list = $variableFactory->createListFromConfiguration([
            '\VariableSourceFixture'
        ]);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testFileNotFoundException()
    {
        $this->expectException(FileNotFoundException::class);

        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/NotExistingFile.php' => '\VariableSourceFixture'
        ]);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testCreateListFromConfigurationWithInvalidClass()
    {
        $this->expectException(ClassNotFoundException::class);

        $variableFactory = new VariableFactory();

        require __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php';

        $list = $variableFactory->createListFromConfiguration([
            '\NotExistingClass'
        ]);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testShortNameGeneration()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertTrue($list->has('doTest2'));
        $this->assertTrue($list->has('test2'));
        $this->assertEquals('value2', $list->get('test2'));

        $this->assertTrue($list->has('runTest3'));
        $this->assertTrue($list->has('test3'));
        $this->assertEquals('value3', $list->get('test3'));

        $this->assertTrue($list->has('getTest4'));
        $this->assertTrue($list->has('test4'));
        $this->assertEquals('value4', $list->get('test4'));

        $this->assertTrue($list->has('setTest5'));
        $this->assertTrue($list->has('test5'));
        $this->assertEquals('value5', $list->get('test5'));
    }

    public function testEvaluated()
    {
        $variableFactory = new VariableFactory();

        $list = $variableFactory->createListFromConfiguration([
            __DIR__ . '/../Fixtures/sources/VariableSourceFixture.php' => '\VariableSourceFixture'
        ]);

        $this->assertEquals('valueEvaluated', $list->get('evaluated'));

        $this->assertTrue(is_callable($list->get('notEvaluated')));
        $callback = $list->get('notEvaluated');
        $this->assertEquals('valueNotEvaluated', call_user_func($callback, 'argument'));
    }
}
