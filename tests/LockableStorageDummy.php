<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests;

use Aes3xs\Yodler\Common\LockableStorage;

class LockableStorageDummy extends LockableStorage
{
    protected $data;

    protected $isLocked = false;

    public function __construct($name = null)
    {
    }

    public function acquire($blocking = false)
    {
        if ($blocking && $this->isLocked) {
            throw new \RuntimeException('Cannot acquire locked storage in blocking mode');
        }

        $this->isLocked = true;

        return true;
    }

    public function release()
    {
        if (!$this->isLocked) {
            throw new \RuntimeException('Cannot release non-locked storage');
        }

        $this->isLocked = false;
    }

    public function read()
    {
        if (!$this->isLocked) {
            throw new \RuntimeException('Cannot read non-locked storage');
        }

        return $this->data;
    }

    public function write($data)
    {
        if (!$this->isLocked) {
            throw new \RuntimeException('Cannot write non-locked storage');
        }

        $this->data = $data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
