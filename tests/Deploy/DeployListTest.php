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

use Aes3xs\Yodler\Deploy\DeployInterface;
use Aes3xs\Yodler\Deploy\DeployList;
use Aes3xs\Yodler\Exception\DeployAlreadyExistsException;
use Aes3xs\Yodler\Exception\DeployNotFoundException;

class DeployListTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $list = new DeployList();
        $deploy1 = $this->createMock(DeployInterface::class);
        $deploy1->method('getName')->willReturn('test1');
        $deploy2 = $this->createMock(DeployInterface::class);
        $deploy2->method('getName')->willReturn('test2');
        $list->add($deploy1);
        $list->add($deploy2);

        $this->assertEquals(['test1' => $deploy1, 'test2' => $deploy2], $list->all());
    }

    public function testAdd()
    {
        $list = new DeployList();
        $deploy = $this->createMock(DeployInterface::class);
        $deploy->method('getName')->willReturn('test');
        $list->add($deploy);

        $this->assertInstanceOf(DeployInterface::class, $list->get('test'));
        $this->assertSame($deploy, $list->get('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(DeployNotFoundException::class);

        $list = new DeployList();

        $list->get('test');
    }

    public function testAlreadyExistException()
    {
        $this->expectException(DeployAlreadyExistsException::class);

        $list = new DeployList();

        $deploy1 = $this->createMock(DeployInterface::class);
        $deploy1->method('getName')->willReturn('test');
        $deploy2 = $this->createMock(DeployInterface::class);
        $deploy2->method('getName')->willReturn('test');
        $list->add($deploy1);
        $list->add($deploy2);
    }
}
