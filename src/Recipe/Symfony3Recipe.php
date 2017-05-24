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

use Aes3xs\Yodler\Service\Releaser;
use Aes3xs\Yodler\Service\Shell;
use Aes3xs\Yodler\Service\Symfony;

/**
 * Example recipe.
 */
class Symfony3Recipe extends Symfony2Recipe
{
    public function createRelease(Releaser $releaser, Symfony $symfony)
    {
        $this->releaseName = $releaser->create();
        $this->releasePath = $releaser->getReleasePath($this->releaseName);
        $symfony->setConsolePath("{$this->releaseName}/bin/console");
        $this->cacheDir = "{$this->releaseName}/var/cache";
    }

    public function updateShared(Releaser $releaser)
    {
        $releaser->updateReleaseShares(['var/logs', 'var/sessions'], ['app/config/parameters.yml'], $this->releaseName);
    }

    public function checkPermissions(Shell $shell)
    {
        $shell->writablePaths(['var/cache', 'var/logs', 'var/sessions'], $this->releasePath);
    }
}
