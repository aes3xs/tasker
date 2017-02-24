<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Commander;

use phpseclib\Net\SFTP;

/**
 * PhpSecLib SFTP factory.
 */
class SftpFactory
{
    /**
     * Create PhpSecLib SFTP connection instance.
     *
     * @param $host
     * @param $port
     *
     * @return SFTP
     */
    public function create($host, $port)
    {
        return new SFTP($host, $port);
    }
}
