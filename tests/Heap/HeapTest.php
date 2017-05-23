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
    protected function createHeap(array $values = [])
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        return new Heap(new VariableList($values), $twig, $expressionLanguage);
    }

    public function testHas()
    {
        $heap = $this->createHeap(['test' => 'value']);

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
        $callback = function () {
            return 'value';
        };
        $heap = $this->createHeap(['test' => $callback]);

        $this->assertEquals('value', $heap->get('test'));
    }

    public function testGetDependent()
    {
        $callback = function () {
            return 'value';
        };
        $dependentCallback = function ($test) {
            return 'value ' . $test;
        };
        $heap = $this->createHeap([
            'test'          => $callback,
            'testDependent' => $dependentCallback,
        ]);

        $this->assertEquals('value value', $heap->get('testDependent'));
    }

    public function testGetCircularReferenceException()
    {
        $this->expectException(VariableCircularReferenceException::class);

        $callback1 = function ($test2) {
            return 'value';
        };
        $callback2 = function ($test1) {
            return 'value';
        };
        $heap = $this->createHeap([
            'test1' => $callback1,
            'test2' => $callback2,
        ]);

        $heap->get('test1');
    }

    public function testResolveString()
    {
        $heap = $this->createHeap(['test' => 'value']);

        $this->assertEquals('Value of test: value', $heap->resolveString('Value of test: {{ test }}'));
    }

    public function testResolveExpression()
    {
        $heap = $this->createHeap(['test' => 5]);

        $this->assertTrue($heap->resolveExpression('test > 4 && test < 6'));
    }

    public function testResolveCallback()
    {
        $callback = function ($test) {
            return 'value' . $test;
        };
        $heap = $this->createHeap(['test' => 123]);

        $this->assertEquals('value123', $heap->resolveCallback($callback));
    }
}
