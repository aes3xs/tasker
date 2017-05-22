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

use Aes3xs\Yodler\Common\LockableStorage;

class LockableStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testReadWrite()
    {
        $storage = new LockableStorage('test');

        $this->assertTrue($storage->acquire());

        $storage->write(['test']);

        $this->assertEquals(['test'], $storage->read());

        $storage->release();
    }

    public function testReadLockException()
    {
        $this->expectException(\RuntimeException::class);

        $storage = new LockableStorage('test');
        $storage->read();
    }

    public function testWriteLockException()
    {
        $this->expectException(\RuntimeException::class);

        $storage = new LockableStorage('test');
        $storage->write([]);
    }
}
