<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Deploy;

use Aes3xs\Yodler\Deploy\BuildInterface;
use Aes3xs\Yodler\Deploy\BuildList;

class BuildListTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndAll()
    {
        $list = new BuildList();
        $build1 = $this->createMock(BuildInterface::class);
        $build2 = $this->createMock(BuildInterface::class);
        $list->add($build1);
        $list->add($build2);

        $this->assertCount(2, $list->all());
        $this->assertSame($build1, $list->all()[0]);
        $this->assertSame($build2, $list->all()[1]);
    }
}
