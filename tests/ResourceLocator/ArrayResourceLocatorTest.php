<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Tests\Resolver;

use Aes3xs\Tasker\Exception\ResourceNotFoundException;
use Aes3xs\Tasker\ResourceLocator\ArrayResourceLocator;

class ArrayResourceLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testHas()
    {
        $locator = new ArrayResourceLocator(['test' => 'value']);

        $this->assertTrue($locator->has('test'));
    }

    public function testHasNot()
    {
        $locator = new ArrayResourceLocator([]);

        $this->assertFalse($locator->has('test'));
    }

    public function testGet()
    {
        $locator = new ArrayResourceLocator(['test' => 'value']);

        $this->assertEquals('value', $locator->get('test'));
    }

    public function testGetException()
    {
        $this->expectException(ResourceNotFoundException::class);

        $locator = new ArrayResourceLocator([]);

        $locator->get('test');
    }
}
