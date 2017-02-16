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

use Aes3xs\Yodler\Exception\VariableNotFoundException;
use Aes3xs\Yodler\Variable\Variable;
use Aes3xs\Yodler\Variable\VariableInterface;
use Aes3xs\Yodler\Variable\VariableList;

class VariableListTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $variable = new Variable('test', 'value');

        $list = new VariableList([$variable]);

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testAdd()
    {
        $list = new VariableList();
        $list->add(new Variable('test', 'value'));

        $this->assertInstanceOf(VariableInterface::class, $list->get('test'));
        $this->assertEquals('value', $list->get('test')->getValue());
    }

    public function testHas()
    {
        $list = new VariableList();

        $this->assertFalse($list->has('test'));

        $list->add(new Variable('test', 'value'));

        $this->assertTrue($list->has('test'));
    }

    public function testGetNotFound()
    {
        $this->expectException(VariableNotFoundException::class);

        $list = new VariableList();

        $list->get('test');
    }
}
