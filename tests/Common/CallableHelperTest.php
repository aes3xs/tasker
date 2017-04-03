<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Common;

use Aes3xs\Yodler\Exception\ArgumentNotFoundException;
use Aes3xs\Yodler\Common\CallableHelper;

class CallableHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractArguments()
    {
        $callable = function ($arg1, $arg2, $arg3) {};

        $arguments = CallableHelper::extractArguments($callable);

        $this->assertEquals(['arg1', 'arg2', 'arg3'], $arguments);
    }

    public function stubMethodToTest($arg1, $arg2)
    {
    }

    public function testExtractMethodArguments()
    {
        $arguments = CallableHelper::extractArguments([$this, 'stubMethodToTest']);

        $this->assertEquals(['arg1', 'arg2'], $arguments);
    }

    public static function stubStaticMethodToTest($arg1, $arg2)
    {
    }

    public function testExtractStaticMethodArguments()
    {
        $arguments = CallableHelper::extractArguments([self::class, 'stubStaticMethodToTest']);

        $this->assertEquals(['arg1', 'arg2'], $arguments);
    }

    public function testCallMissingArgument()
    {
        $this->expectException(ArgumentNotFoundException::class);

        $callable = function ($arg1, $arg2) {
            return $arg1 + $arg2;
        };

        CallableHelper::call($callable, ['arg1' => 1]);
    }

    public function testCall()
    {
        $callable = function ($arg1, $arg2) {
            return $arg1 + $arg2;
        };

        $result = CallableHelper::call($callable, ['arg1' => 1, 'arg2' => 2]);

        $this->assertEquals(3, $result);
    }
}
