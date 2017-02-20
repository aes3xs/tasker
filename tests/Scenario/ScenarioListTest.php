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

use Aes3xs\Yodler\Exception\ScenarioAlreadyExistsException;
use Aes3xs\Yodler\Exception\ScenarioNotFoundException;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Scenario\ScenarioList;

class ScenarioListTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $list = new ScenarioList();
        $connection1 = $this->createMock(ScenarioInterface::class);
        $connection1->method('getName')->willReturn('test1');
        $connection2 = $this->createMock(ScenarioInterface::class);
        $connection2->method('getName')->willReturn('test2');
        $list->add($connection1);
        $list->add($connection2);

        $this->assertEquals(['test1' => $connection1, 'test2' => $connection2], $list->all());
    }

    public function testAdd()
    {
        $list = new ScenarioList();
        $connection = $this->createMock(ScenarioInterface::class);
        $connection->method('getName')->willReturn('test');
        $list->add($connection);

        $this->assertInstanceOf(ScenarioInterface::class, $list->get('test'));
        $this->assertSame($connection, $list->get('test'));
    }

    public function testNotFoundException()
    {
        $this->expectException(ScenarioNotFoundException::class);

        $list = new ScenarioList();

        $list->get('test');
    }

    public function testAlreadyExistException()
    {
        $this->expectException(ScenarioAlreadyExistsException::class);

        $list = new ScenarioList();

        $connection1 = $this->createMock(ScenarioInterface::class);
        $connection1->method('getName')->willReturn('test');
        $connection2 = $this->createMock(ScenarioInterface::class);
        $connection2->method('getName')->willReturn('test');
        $list->add($connection1);
        $list->add($connection2);
    }
}
