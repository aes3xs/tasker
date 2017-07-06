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

use Aes3xs\Yodler\Exception\ParameterAlreadyExistsException;
use Aes3xs\Yodler\Exception\ParameterNotFoundException;
use Aes3xs\Yodler\ParameterList;

class ParameterListTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $list = new ParameterList(['test' => 'value']);

        $this->assertEquals('value', $list->get('test'));
    }

    public function testAddAndGet()
    {
        $list = new ParameterList();
        $list->add('test', 'value');

        $this->assertEquals('value', $list->get('test'));
    }

    public function testAll()
    {
        $list = new ParameterList();
        $list->add('test1', 'value1');
        $list->add('test2', 'value2');

        $this->assertEquals([
            'test1' => 'value1',
            'test2' => 'value2',
        ], $list->all());
    }

    public function testHas()
    {
        $list = new ParameterList();

        $this->assertFalse($list->has('test'));

        $list->add('test', 'value');

        $this->assertTrue($list->has('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(ParameterNotFoundException::class);

        $list = new ParameterList();

        $list->get('test');
    }

    public function testAlreadyExistException()
    {
        $this->expectException(ParameterAlreadyExistsException::class);

        $list = new ParameterList();
        $list->add('test1', 'value1');
        $list->add('test1', 'value1');
    }
}
