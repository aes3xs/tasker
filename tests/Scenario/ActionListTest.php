<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Scenario;

use Aes3xs\Yodler\Scenario\ActionInterface;
use Aes3xs\Yodler\Scenario\ActionList;

class ActionListTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndAll()
    {
        $list = new ActionList();
        $action1 = $this->createMock(ActionInterface::class);
        $action2 = $this->createMock(ActionInterface::class);
        $list->add($action1);
        $list->add($action2);

        $this->assertCount(2, $list->all());
        $this->assertSame($action1, $list->all()[0]);
        $this->assertSame($action2, $list->all()[1]);
    }
}
