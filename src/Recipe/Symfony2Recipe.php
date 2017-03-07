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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
    }

    public function lock(Releaser $releaser, InputInterface $input, OutputInterface $output, $deploy_path)
    {
        if ($releaser->isLocked($deploy_path)) {
            $helper = new QuestionHelper();
            $question = new ConfirmationQuestion('<info>Deploy is locked. Unlock?</info> <comment>(Y/n)</comment> ');
            if ($helper->ask($input, $output, $question)) {
                $releaser->unlock($deploy_path);
            }
        }
        $releaser->lock($deploy_path);
    }

    public function createRelease(Releaser $releaser, $deploy_path)
    {
        $this->releaseName = $releaser->create($deploy_path);
        $this->releasePath = $releaser->getReleasePath($deploy_path, $this->releaseName);
        $this->console = "{$this->releasePath}/app/console";
        $this->cacheDir = "{$this->releasePath}/app/cache";
    }

    public function updateCode(Git $git, $repository, $branch, Releaser $releaser, $deploy_path)
    {
        $releases = $releaser->getReleaseList($deploy_path);
        $release = $releases ? reset($releases) : null;
        $reference = $release ? $releaser->getReleasePath($deploy_path, $release) : null;

        $git->cloneAt($repository, $this->releasePath, $branch, $reference);
    }

    public function checkPaths(Shell $shell)
    {
        $this->removePaths($shell, ['web/app_*.php', 'web/config.php'], $this->releasePath);

        if ($shell->exists($this->cacheDir)) {
            $shell->rm($this->cacheDir);
        }
        $shell->mkdir($this->cacheDir);
        $shell->chmod($this->cacheDir, 0775);
    }

    public function updateShared(Releaser $releaser, $deploy_path)
    {
        $releaser->updateReleaseShares($deploy_path, $this->releaseName, ['app/logs'], ['app/config/parameters.yml']);
    }

    public function installVendors(Composer $composer)
    {
        $composer->install($this->releasePath);
    }

    public function warmCache(Symfony $symfony)
    {
        $symfony->setDefaultOptions(Symfony::DEFAULT_OPTIONS);
        $symfony->runCommand($this->console, 'cache:warmup');
    }

    public function installAssets(Symfony $symfony, $assetic_dump = false)
    {
        $symfony->runCommand($this->console, 'assets:install', [$this->releasePath . "/web"]);

        if ($assetic_dump) {
            $symfony->runCommand($this->console, 'assetic:dump');
        }
    }

    public function checkPermissions(Shell $shell)
    {
        $this->writablePaths($shell, ['app/cache', 'app/logs'], $this->releasePath);
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
        $releaser->unlock($deploy_path);
    }

    public function cleanup(Releaser $releaser, $deploy_path, $keep_releases = 5)
    {
        $releaser->cleanup($deploy_path, $keep_releases);
    }
}
