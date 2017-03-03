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

    protected function removePaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $path) {
            $shell->rm($basePath ? "$basePath/$path" : $path);
        }
    }

    protected function copyPaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $source => $target) {
            $shell->copy(
                $basePath ? "$basePath/$source" : $source,
                $basePath ? "$basePath/$target" : $target
            );
        }
    }

    protected function createPaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $path) {
            $shell->mkdir($basePath ? "$basePath/$path" : $path);
        }
    }

    protected function linkPaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $source => $target) {
            $shell->ln(
                $basePath ? "$basePath/$source" : $source,
                $basePath ? "$basePath/$target" : $target
            );
        }
    }

    protected function writablePaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $path) {
            if (!$shell->isWritable($basePath ? "$basePath/$path" : $path)) {
                throw new \RuntimeException('Path not writable: ' . ($basePath ? "$basePath/$path" : $path));
            }
        }
    }

    protected function readablePaths(Shell $shell, array $paths, $basePath = null)
    {
        foreach ($paths as $path) {
            if (!$shell->isReadable($basePath ? "$basePath/$path" : $path)) {
                throw new \RuntimeException('Path not readable: ' . ($basePath ? "$basePath/$path" : $path));
            }
        }
    }
}
