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
use Aes3xs\Yodler\Variable\VariableInterface;
use Aes3xs\Yodler\Variable\VariableList;

class VariableListTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $variable = $this->createMock(VariableInterface::class);
        $variable->method('getName')->willReturn('test');

        $list = new VariableList([$variable]);

        $this->assertSame($variable, $list->get('test'));
    }

    public function testAddAndGet()
    {
        $list = new VariableList();
        $variable = $this->createMock(VariableInterface::class);
        $variable->method('getName')->willReturn('test');
        $list->add($variable);

        $this->assertSame($variable, $list->get('test'));
    }

    public function testAll()
    {
        $list = new VariableList();
        $variable1 = $this->createMock(VariableInterface::class);
        $variable1->method('getName')->willReturn('test1');
        $variable2 = $this->createMock(VariableInterface::class);
        $variable2->method('getName')->willReturn('test2');
        $list->add($variable1);
        $list->add($variable2);

        $this->assertCount(2, $list->all());
        $this->assertSame($variable1, $list->all()['test1']);
        $this->assertSame($variable2, $list->all()['test2']);
    }

    public function testHas()
    {
        $list = new VariableList();

        $this->assertFalse($list->has('test'));

        $variable = $this->createMock(VariableInterface::class);
        $variable->method('getName')->willReturn('test');
        $list->add($variable);

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
        $variable1 = $this->createMock(VariableInterface::class);
        $variable1->method('getName')->willReturn('test');
        $variable2 = $this->createMock(VariableInterface::class);
        $variable2->method('getName')->willReturn('test');

        $list->add($variable1);
        $list->add($variable2);
    }
}
