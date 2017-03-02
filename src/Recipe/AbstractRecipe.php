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
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    protected function removePaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $path) {
            $shell->rm("$basePath/$path");
        }
    }

    protected function copyPaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $source => $target) {
            $shell->copy("$basePath/$source", "$basePath/$target");
        }
    }

    protected function createPaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $path) {
            $shell->mkdir("$basePath/$path");
        }
    }

    protected function linkPaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $source => $target) {
            $shell->ln("$basePath/$source", "$basePath/$target");
        }
    }

    protected function writablePaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $path) {
            if (!$shell->isWritable("$basePath/$path")) {
                throw new \RuntimeException('Path not writable: ' . "$basePath/$path");
            }
        }
    }

    protected function readablePaths(Shell $shell, $basePath, array $paths)
    {
        foreach ($paths as $path) {
            if (!$shell->isReadable("$basePath/$path")) {
                throw new \RuntimeException('Path not readable: ' . "$basePath/$path");
            }
        }
    }
}
