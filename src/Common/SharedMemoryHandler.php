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

/**
 * Implements shared memory feature to share data between deploy processes.
 *
 * Stores data in JSON format.
 * Uses temporary file.
 */
class SharedMemoryHandler
{
    /**
     * @var string
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->file = sys_get_temp_dir() . '/_yodler_' . $name;

        touch($this->file);
        chmod($this->file, 0777);
    }

    /**
     * Return shared memory data.
     *
     * If shared memory has no data, return null.
     *
     * @return mixed|null
     */
    public function read()
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $content = file_get_contents($this->file);

        if (!$content) {
            return null;
        }

        $data = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_decode error: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Write data into shared memory.
     *
     * @param $data
     */
    public function write($data)
    {
        $content = json_encode($data);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_encode error: ' . json_last_error_msg());
        }

        file_put_contents($this->file, $content);
    }

    public function delete()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
