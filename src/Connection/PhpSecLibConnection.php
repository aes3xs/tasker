<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Connection;

use Aes3xs\Tasker\Exception\PhpSecLibCommandException;
use phpseclib\Net\SFTP;

/**
 * PhpSecLib connection implementation.
 */
class PhpSecLibConnection implements ConnectionInterface
{
    const TIMEOUT = 1200;

    /**
     * @var SFTP
     */
    protected $sftp;

    /**
     * Constructor.
     *
     * @param SFTP $sftp
     */
    public function __construct(SFTP $sftp)
    {
        $this->sftp = $sftp;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        // Silence error reporting
        set_error_handler(function () {});
        $this->sftp->setTimeout(self::TIMEOUT);
        $output = $this->sftp->exec($command);
        restore_error_handler();

        if ($this->sftp->getExitStatus() !== 0) {
            $e = new PhpSecLibCommandException($command);
            $e->addError($this->sftp->getSFTPErrors());
            $e->addError($output);
            $e->addError($this->sftp->getStdError());
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        // Silence error reporting
        set_error_handler(function () {});
        $result = $this->sftp->put($remote, $local, SFTP::SOURCE_LOCAL_FILE);
        restore_error_handler();

        if (!$result) {
            $e = new PhpSecLibCommandException(sprintf('Error put: from local "%s" to remote "%s"', $local, $remote));
            $e->addError($this->sftp->getSFTPErrors());
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        // Silence error reporting
        set_error_handler(function () {});
        $result = $this->sftp->get($remote, $local);
        restore_error_handler();

        if (!$result) {
            $e = new PhpSecLibCommandException(sprintf('Error recv: from remote "%s" to local "%s"', $remote, $local));
            $e->addError($this->sftp->getSFTPErrors());
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }
    }
}
