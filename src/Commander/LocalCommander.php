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

use Aes3xs\Yodler\Common\ProcessFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Local commander implementation.
 */
class LocalCommander implements CommanderInterface
{
    const TIMEOUT = 1200;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     * @param ProcessFactory $processFactory
     */
    public function __construct(Filesystem $filesystem, ProcessFactory $processFactory)
    {
        $this->filesystem = $filesystem;
        $this->processFactory = $processFactory;
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

        $process = $this->processFactory->create($command);
        $process->setTimeout(self::TIMEOUT);
        $process->setIdleTimeout(self::TIMEOUT);
        $process->mustRun();
        $output = $process->getOutput() ?: $process->getErrorOutput();

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

        $this->filesystem->copy($local, $remote, true);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        if ($this->logger) {
            $this->logger->debug('Recv: ' . $remote . ' to ' . $local);
        }

        $this->filesystem->copy($remote, $local, true);
    }
}
