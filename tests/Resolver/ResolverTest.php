<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Resolver;

use Aes3xs\Yodler\Exception\ResourceCircularReferenceException;
use Aes3xs\Yodler\Exception\ResourceNotFoundException;
use Aes3xs\Yodler\Resolver\ResourceResolver;
use Aes3xs\Yodler\ResourceLocator\ArrayResourceLocator;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function createResolver(array $values = [])
    {
        $resolver = new ResourceResolver(new ArrayResourceLocator($values));

        return $resolver;
    }

    public function testNotFoundException()
    {
        $this->expectException(ResourceNotFoundException::class);

        $resolver = $this->createResolver();
        $resolver->resolveResource('test');
    }

    public function testResolveResource()
    {
        $resolver = $this->createResolver(['test' => 'value']);

        $this->assertEquals('value', $resolver->resolveResource('test'));
    }

    public function testResolveCallbackResource()
    {
        $callback = function () {
            return 'value';
        };
        $resolver = $this->createResolver(['test' => $callback]);

        $this->assertEquals('value', $resolver->resolveResource('test'));
    }

    public function testGetDependent()
    {
        $callback = function () {
            return 'value';
        };
        $dependentCallback = function ($test) {
            return 'value ' . $test;
        };
        $resolver = $this->createResolver([
            'test'          => $callback,
            'testDependent' => $dependentCallback,
        ]);

        $this->assertEquals('value value', $resolver->resolveResource('testDependent'));
    }

    public function testGetCircularReferenceException()
    {
        $this->expectException(ResourceCircularReferenceException::class);

        $callback1 = function ($test2) {
            return 'value';
        };
        $callback2 = function ($test1) {
            return 'value';
        };
        $resolver = $this->createResolver([
            'test1' => $callback1,
            'test2' => $callback2,
        ]);

        $resolver->resolveResource('test1');
    }

    public function testResolveString()
    {
        $resolver = $this->createResolver(['test' => 'value']);

        $this->assertEquals('Value of test: value', $resolver->resolveString('Value of test: {{ test }}'));
    }

    public function testResolveCallback()
    {
        $callback = function ($test) {
            return 'value' . $test;
        };
        $resolver = $this->createResolver(['test' => 123]);

        $this->assertEquals('value123', $resolver->resolveCallback($callback));
    }
}
