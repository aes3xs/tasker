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

use Aes3xs\Yodler\Exception\VariableAlreadyExistsException;
use Aes3xs\Yodler\Exception\VariableNotFoundException;
use Aes3xs\Yodler\Variable\VariableList;

class VariableListTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $list = new VariableList(['test' => 'value']);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testAddAndGet()
    {
        $list = new VariableList();
        $list->add('test', 'value');

        $this->assertEquals('value', $list->get('test'));
    }

    public function testAll()
    {
        $list = new VariableList();
        $list->add('test1', 'value1');
        $list->add('test2', 'value2');

        $this->assertEquals([
            'test1' => 'value1',
            'test2' => 'value2',
        ], $list->all());
    }

    public function testHas()
    {
        $list = new VariableList();

        $this->assertFalse($list->has('test'));

        $list->add('test', 'value');

        $this->assertTrue($list->has('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(VariableNotFoundException::class);

        $list = new VariableList();

        $list->get('test');
    }

    public function testAlreadyExistException()
    {
        $this->expectException(VariableAlreadyExistsException::class);

        $list = new VariableList();
        $list->add('test1', 'value1');
        $list->add('test1', 'value1');
    }
}
