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

/**
 * Example recipe.
 */
class Symfony3Recipe extends Symfony2Recipe
{
    public function createRelease(Releaser $releaser, $deploy_path)
    {
        parent::createRelease($releaser, $deploy_path);

        $this->console = "{$this->releaseName}/bin/console";
        $this->cacheDir = "{$this->releaseName}/var/cache";
    }

    public function updateShared(Releaser $releaser, $deploy_path)
    {
        $releaser->updateReleaseShares($deploy_path, ['var/logs', 'var/sessions'], ['app/config/parameters.yml'], $this->releaseName);
    }

    public function checkPermissions(Shell $shell)
    {
        $this->writablePaths($shell, ['var/cache', 'var/logs', 'var/sessions'], $this->releasePath);
    }
}
