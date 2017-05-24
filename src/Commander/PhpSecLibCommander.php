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
use Psr\Log\LoggerInterface;

/**
 * PhpSecLib commander implementation.
 */
class PhpSecLibCommander implements CommanderInterface
{
    const TIMEOUT = 300;

    /**
     * @var SFTP
     */
    protected $sftp;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        if ($this->logger) {
            $this->logger->debug('> ' . $command);
        }

        // Silence error reporting
        set_error_handler(function () {});
        $this->sftp->setTimeout(self::TIMEOUT);
        $output = $this->sftp->exec($command);
        restore_error_handler();

        if ($this->sftp->getExitStatus() !== 0) {
            $e = new PhpSecLibCommandException($command);
            $e->addError($this->sftp->getSFTPErrors());
            $e->addError($this->sftp->getStdError() ?: $output);
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }

        if ($this->logger) {
            $this->logger->debug('< ' . $command . ': ' . $output);
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        if ($this->logger) {
            $this->logger->debug('Send: ' . $local . ' to ' . $remote);
        }

        // Silence error reporting
        set_error_handler(function () {});
        $result = $this->sftp->put($remote, $local, SFTP::SOURCE_LOCAL_FILE);
        restore_error_handler();

        if (!$result) {
            $e = new PhpSecLibCommandException('Error put: from local "%s" to remote "%s"', $local, $remote);
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
        if ($this->logger) {
            $this->logger->debug('Recv: ' . $remote . ' to ' . $local);
        }

        // Silence error reporting
        set_error_handler(function () {});
        $result = $this->sftp->get($remote, $local);
        restore_error_handler();

        if (!$result) {
            $e = new PhpSecLibCommandException('Error recv: from remote "%s" to local "%s"', $remote, $local);
            $e->addError($this->sftp->getSFTPErrors());
            if ($error = error_get_last()) {
                $e->addError($error['message']);
            }
            throw $e;
        }
    }
}
