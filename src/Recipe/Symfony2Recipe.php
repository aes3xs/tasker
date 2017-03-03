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

use Aes3xs\Yodler\Service\Composer;
use Aes3xs\Yodler\Service\Git;
use Aes3xs\Yodler\Service\Releaser;
use Aes3xs\Yodler\Service\Shell;
use Aes3xs\Yodler\Service\Symfony;

/**
 * Example recipe.
 */
class Symfony2Recipe extends AbstractRecipe
{
    protected $releaseName;
    protected $releasePath;
    protected $console;
    protected $cacheDir;

    public function prepare(Releaser $releaser, $deploy_path)
    {
        $releaser->prepare($deploy_path);
        $releaser->lock($deploy_path);
        $this->releaseName = $releaser->create($deploy_path);
        $this->releasePath = $releaser->getReleasePath($deploy_path, $this->releaseName);
        $this->console = "{$this->releaseName}/app/console";
        $this->cacheDir = "{$this->releaseName}/app/cache";
    }

    public function updateCode(Git $git, $repository, $branch, Releaser $releaser, $deploy_path)
    {
        $releases = $releaser->getReleaseList($deploy_path);
        $reference = $releases ? reset($releases) : null;

        $git->cloneAt($repository, $this->releasePath, $branch, $reference);
    }

    public function structureCheck(Shell $shell)
    {
        $this->removePaths($shell, $this->releasePath, ['web/app_*.php', 'web/config.php']);

        if ($shell->exists($this->cacheDir)) {
            $shell->rm($this->cacheDir);
        }
        $shell->mkdir($this->cacheDir);
        $shell->chmod($this->cacheDir, 0775);
    }

    public function shared(Releaser $releaser, $deploy_path)
    {
        $releaser->updateReleaseShares($deploy_path, $this->releaseName, ['app/logs'], ['app/config/parameters.yml']);
    }

    public function composerInstall(Composer $composer)
    {
        $composer->install($this->releaseName);
    }

    public function cacheWarmup(Symfony $symfony)
    {
        $symfony->runCommand($this->console, 'cache:warmup');
    }

    public function assets(Symfony $symfony, $assetic_dump = false)
    {
        $symfony->runCommand($this->console, 'assets:install', [$this->releaseName . "/web"]);

        if ($assetic_dump) {
            $symfony->runCommand($this->console, 'assetic:dump');
        }
    }

    public function permissionCheck(Shell $shell)
    {
        $this->writablePaths($shell, $this->releasePath, ['app/cache', 'app/logs']);
    }

    public function migrate(Symfony $symfony, $migrate = false)
    {
        if ($migrate) {
            $symfony->runCommand($this->console, 'doctrine:migrations:migrate', [], array_merge(Symfony::DEFAULT_OPTIONS, ['allow-no-migration']));
        }
    }

    public function release(Releaser $releaser, $deploy_path)
    {
        $releaser->release($deploy_path, $this->releaseName);
    }

    public function unlock(Releaser $releaser, $deploy_path)
    {
        $releaser->unlock($deploy_path);
    }

    public function cleanup(Releaser $releaser, $deploy_path, $keep_releases = 5)
    {
        $releaser->cleanup($deploy_path, $keep_releases);
    }
}
