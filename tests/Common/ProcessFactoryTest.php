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

use Aes3xs\Yodler\Common\ProcessFactory;
use Symfony\Component\Process\Process;

class ProcessFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $processFactory = new ProcessFactory();

        $process = $processFactory->create('command');

        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals('command', $process->getCommandLine());
    }
}
