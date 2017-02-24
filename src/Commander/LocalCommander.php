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
use Symfony\Component\Filesystem\Filesystem;

class LocalCommander implements CommanderInterface
{
    const TIMEOUT = 300;

    protected $filesystem;
    protected $processFactory;

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

        return $process->getOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        $this->filesystem->copy($local, $remote);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $this->filesystem->copy($remote, $local);
    }
}
