<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Service;

/**
 * Helper service to some common tasks.
 */
class Common
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * Constructor.
     *
     * @param Shell $shell
     */
    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    /**
     * @param $basePath
     * @param array $removePaths
     * @param array $copyPaths
     * @param array $createPaths
     * @param array $linkPaths
     * @param array $writablePaths
     * @param array $readablePaths
     */
    public function structurize(
        $basePath,
        array $removePaths = [],
        array $copyPaths = [],
        array $createPaths = [],
        array $linkPaths = [],
        array $writablePaths = [],
        array $readablePaths = []
    ) {
        foreach ($removePaths as $path) {
            $this->shell->rm("$basePath/$path");
        }

        foreach ($createPaths as $path) {
            $this->shell->mkdir("$basePath/$path");
        }

        foreach ($copyPaths as $source => $target) {
            $this->shell->copy("$basePath/$source", "$basePath/$target");
        }

        foreach ($linkPaths as $source => $target) {
            $this->shell->ln("$basePath/$source", "$basePath/$target");
        }

        foreach ($writablePaths as $path) {
            if (!$this->shell->isWritable("$basePath/$path")) {
                throw new \RuntimeException('Path not writable: ' . "$basePath/$path");
            }
        }

        foreach ($readablePaths as $path) {
            if (!$this->shell->isReadable("$basePath/$path")) {
                throw new \RuntimeException('Path not readable: ' . "$basePath/$path");
            }
        }
    }


}
