<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Recipe;

use Aes3xs\Tasker\Service\Releaser;
use Aes3xs\Tasker\Service\Shell;
use Aes3xs\Tasker\Service\Symfony;

/**
 * Example recipe.
 */
class Symfony3Recipe extends Symfony2Recipe
{
    public function warmCache(Symfony $symfony)
    {
        $symfony->setConsolePath("{$this->releaseName}/bin/console");

        $symfony->runCommand('cache:warmup');
    }

    public function updateShared(Releaser $releaser)
    {
        $releaser->updateReleaseShares(['var/logs', 'var/sessions'], ['app/config/parameters.yml'], $this->releaseName);
    }

    public function checkPermissions(Shell $shell)
    {
        $shell->isWritablePaths(['var/cache', 'var/logs', 'var/sessions'], $this->releasePath);
    }
}
