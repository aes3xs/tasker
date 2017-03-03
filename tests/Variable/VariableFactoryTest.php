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
}
