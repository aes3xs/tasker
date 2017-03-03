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

use Aes3xs\Yodler\Exception\VariableCircularReferenceException;
use Aes3xs\Yodler\Exception\VariableNotFoundException;
use Aes3xs\Yodler\Heap\Heap;
use Aes3xs\Yodler\Variable\VariableList;
use Symfony\Component\DependencyInjection\ExpressionLanguage;

class HeapTest extends \PHPUnit_Framework_TestCase
{
    protected function createHeap()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        return new Heap($twig, $expressionLanguage);
    }

    public function testAddVariables()
    {
        $heap = $this->createHeap();
        $list1 = new VariableList(['test' => 'value']);
        $list2 = new VariableList(['test' => 'overridenValue']);

        $heap->addVariables($list1);

        $this->assertEquals('value', $heap->get('test'));

        $heap->addVariables($list2);

        $this->assertEquals('overridenValue', $heap->get('test'));
    }

    public function testHas()
    {
        $heap = $this->createHeap();

        $this->assertFalse($heap->has('test'));

        $list = new VariableList(['test' => 'value']);
        $heap->addVariables($list);

        $this->assertTrue($heap->has('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(VariableNotFoundException::class);

        $heap = $this->createHeap();
        $heap->get('test');
    }

    public function testGet()
    {
        $heap = $this->createHeap();
        $callback = function () {
            return 'value';
        };
        $list = new VariableList(['test' => $callback]);
        $heap->addVariables($list);

        $this->assertEquals('value', $heap->get('test'));
    }

    public function testGetDependent()
    {
        $heap = $this->createHeap();
        $callback = function () {
            return 'value';
        };
        $dependentCallback = function ($test) {
            return 'value ' . $test;
        };
        $list = new VariableList([
            'test'          => $callback,
            'testDependent' => $dependentCallback,
        ]);
        $heap->addVariables($list);

        $this->assertEquals('value value', $heap->get('testDependent'));
    }

    public function testGetCircularReferenceException()
    {
        $this->expectException(VariableCircularReferenceException::class);

        $heap = $this->createHeap();
        $callback1 = function ($test2) {
            return 'value';
        };
        $callback2 = function ($test1) {
            return 'value';
        };
        $list = new VariableList([
            'test1' => $callback1,
            'test2' => $callback2,
        ]);
        $heap->addVariables($list);

        $heap->get('test1');
    }

    public function testResolveString()
    {
        $heap = $this->createHeap();
        $list = new VariableList(['test' => 'value']);
        $heap->addVariables($list);

        $this->assertEquals('Value of test: value', $heap->resolveString('Value of test: {{ test }}'));
    }

    public function testResolveExpression()
    {
        $heap = $this->createHeap();
        $list = new VariableList(['test' => 5]);
        $heap->addVariables($list);

        $this->assertTrue($heap->resolveExpression('test > 4 && test < 6'));
    }

    public function testResolveCallback()
    {
        $heap = $this->createHeap();
        $callback = function ($test) {
            return 'value' . $test;
        };
        $list = new VariableList([
            'test' => 123,
        ]);
        $heap->addVariables($list);

        $this->assertEquals('value123', $heap->resolveCallback($callback));
    }
}
