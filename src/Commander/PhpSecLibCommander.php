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

use Aes3xs\Yodler\Exception\PhpSecLibCommandException;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;

class PhpSecLibCommander implements CommanderInterface
{
    /**
     * @var SFTP
     */
    protected $sftp;

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
        $result = $this->sftp->exec($command);
        restore_error_handler();

        if ($this->sftp->getExitStatus() !== 0) {
            $e = new PhpSecLibCommandException(__METHOD__, func_get_args());
            $e->addError($this->sftp->getSFTPErrors());
            $e->addError($this->sftp->getStdError() ?: $result);
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }

        return $result;
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
            $e = new PhpSecLibCommandException(__METHOD__, func_get_args());
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
            $e = new PhpSecLibCommandException(__METHOD__, func_get_args());
            $e->addError($this->sftp->getSFTPErrors());
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }
    }
}
