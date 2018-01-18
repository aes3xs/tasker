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

use Symfony\Component\Filesystem\Filesystem;

/**
 * Local connection implementation.
 */
class LocalConnection implements ConnectionInterface
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
    public function exec($command)
    {
        $process = $this->processFactory->create($command);
        $process->setTimeout(self::TIMEOUT);
        $process->setIdleTimeout(self::TIMEOUT);
        $process->mustRun();
        $output = $process->getOutput() ?: $process->getErrorOutput();

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        $this->filesystem->copy($local, $remote, true);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $this->filesystem->copy($remote, $local, true);
    }
}
