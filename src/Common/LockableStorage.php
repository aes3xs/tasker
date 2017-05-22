<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Common;

use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\SemaphoreStore;

class LockableStorage
{
    /**
     * @var LockInterface
     */
    protected $lock;

    /**
     * @var SharedMemoryHandler
     */
    protected $shm;

    /**
     * Constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $store = new SemaphoreStore();
        $factory = new Factory($store);

        $this->lock = $factory->createLock($name);

        $this->shm = new SharedMemoryHandler($name);

        $this->shm->delete();
    }

    /**
     * Acquires the lock.
     *
     * @param bool $blocking
     * @return bool
     */
    public function acquire($blocking = false)
    {
        return $this->lock->acquire($blocking);
    }

    /**
     * Release the lock.
     */
    public function release()
    {
        $this->lock->release();
    }

    /**
     * Return lock data.
     * If lock has no data, return null.
     *
     * @return mixed|null
     */
    public function read()
    {
        if (!$this->lock->isAcquired()) {
            throw new RuntimeException('Lock must be acquired before reading');
        }

        return $this->shm->read();
    }

    /**
     * Write lock data.
     *
     * @param $data
     */
    public function write($data)
    {
        if (!$this->lock->isAcquired()) {
            throw new RuntimeException('Lock must be acquired before writing');
        }

        $this->shm->write($data);
    }
}
