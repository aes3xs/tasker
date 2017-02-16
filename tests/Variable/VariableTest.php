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

use Aes3xs\Yodler\Variable\Variable;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $variable = new Variable('test', 'value');

        $this->assertEquals('test', $variable->getName());
        $this->assertEquals('value', $variable->getValue());
    }
}
