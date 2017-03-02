<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Recipe;

use Aes3xs\Yodler\Service\Shell;

abstract class AbstractRecipe implements RecipeInterface
{
    public function __construct()
    {
    }

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $releaseName;

    /**
     * @var string
     */
    protected $releasePath;

    /**
     * @return Shell
     */
    protected function getShell()
    {
        if (!$this->shell instanceof Shell) {
            throw new \RuntimeException('Shell is not properly initialzed');
        }

        return $this->shell;
    }

    protected function getReleasePath()
    {
        if (!$this->releasePath) {
            throw new \RuntimeException('Release path is not properly initialzed');
        }

        return $this->releasePath;
    }

    /**
     * @param array $paths
     */
    protected function removePaths(array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $path) {
            $this->getShell()->rm("$basePath/$path");
        }
    }

    /**
     * @param array $paths
     */
    protected function copyPaths(array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $source => $target) {
            $this->shell->copy("$basePath/$source", "$basePath/$target");
        }
    }

    /**
     * @param array $paths
     */
    protected function createPaths(array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $path) {
            $this->shell->mkdir("$basePath/$path");
        }
    }

    /**
     * @param array $paths
     */
    protected function linkPaths(array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $source => $target) {
            $this->shell->ln("$basePath/$source", "$basePath/$target");
        }
    }

    /**
     * @param array $paths
     */
    protected function writablePaths( array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $path) {
            if (!$this->shell->isWritable("$basePath/$path")) {
                throw new \RuntimeException('Path not writable: ' . "$basePath/$path");
            }
        }
    }

    /**
     * @param array $paths
     */
    protected function readablePaths(array $paths = [])
    {
        $basePath = $this->getReleasePath();
        foreach ($paths as $path) {
            if (!$this->shell->isReadable("$basePath/$path")) {
                throw new \RuntimeException('Path not readable: ' . "$basePath/$path");
            }
        }
    }
}
