<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Helper;

use Aes3xs\Yodler\Common\SharedMemoryHandler;

/**
 * This test doesn't cover interprocess communication.
 */
class SharedMemoryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAndDump()
    {
        $sharedMemoryhandler = new SharedMemoryHandler('test');

        $sharedMemoryhandler->dump(['value']);

        $this->assertEquals(['value'], $sharedMemoryhandler->read());
    }
}
