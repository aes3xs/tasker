<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Reporter;

use Aes3xs\Yodler\Reporter\Reporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testReportAction()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportAction('test');

        $expected = [
            'name'    => 'test',
            'state'   => 'None',
            'start'   => null,
            'finish'  => null,
            'output'  => null,
            'isGroup' => false,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);

        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));
    }

    public function testReportActionGroup()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportActionGroup('test');

        $expected = [
            'name'    => 'test',
            'isGroup' => true,
            'state'   => null,
            'start'   => null,
            'finish'  => null,
            'output'  => null,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));
    }

    public function testReportActionRunning()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportActionRunning('test');

        $expected = [
            'name'    => 'test',
            'state'   => 'Running',
            'finish'  => null,
            'output'  => null,
            'isGroup' => false,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));

        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['start']); // Ignore seconds
    }

    public function testReportActionSucceed()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportActionSucceed('test', 'output');

        $expected = [
            'name'    => 'test',
            'state'   => 'Succeed',
            'output'  => 'output',
            'start'   => NULL,
            'isGroup' => false,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));

        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['finish']); // Ignore seconds
    }

    public function testReportActionError()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportActionError('test', new \RuntimeException('exception'));

        $expected = [
            'name'    => 'test',
            'state'   => 'Error',
            'output'  => 'exception',
            'start'   => null,
            'isGroup' => false,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));

        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['finish']); // Ignore seconds
    }

    public function testReportActionSkipped()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportActionSkipped('test');

        $expected = [
            'name'    => 'test',
            'state'   => 'Skipped',
            'start'   => null,
            'output'  => null,
            'isGroup' => false,
        ];
        $actions = $reporter->retrieveActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));

        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['finish']); // Ignore seconds
    }

    public function testActionOverride()
    {
        $style = $this->createMock(SymfonyStyle::class);
        $logger = $this->createMock(LoggerInterface::class);

        $reporter = new Reporter($style, $logger);

        $reporter->reportAction('test');
        $reporter->reportActionRunning('test');
        $reporter->reportActionSucceed('test', 'output');

        $actions = $reporter->retrieveActions();

        $expected = [
            'name'    => 'test',
            'state'   => 'Succeed',
            'output'  => 'output',
            'isGroup' => false,
        ];

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey('test', $actions);
        $this->assertEquals($expected, array_intersect_key($actions['test'], $expected));

        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['start']); // Ignore seconds
        $this->assertRegExp(date('/Y\-m\-d H\:i\:[0-9]{2}/'), $actions['test']['finish']); // Ignore seconds
    }
}
