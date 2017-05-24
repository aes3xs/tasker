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
class Symfony2Recipe
{
    protected $releaseName;
    protected $releasePath;

    public function prepare(Releaser $releaser, $deploy_path)
    {
        $releaser->setDeployPath($deploy_path);
        $releaser->prepare();
    }

    public function lock(Releaser $releaser, InputInterface $input, OutputInterface $output)
    {
        if ($releaser->isLocked()) {
            $helper = new QuestionHelper();
            $question = new ConfirmationQuestion('<info>Deploy is locked. Unlock?</info> <comment>(Y/n)</comment> ');
            if ($helper->ask($input, $output, $question)) {
                $releaser->unlock();
            }
        }
        $releaser->lock();
    }

    public function createRelease(Releaser $releaser)
    {
        $this->releaseName = $releaser->create();
        $this->releasePath = $releaser->getReleasePath($this->releaseName);
    }

    public function updateCode(Git $git, $repository, $branch, Releaser $releaser)
    {
        $releases = $releaser->getReleaseList();
        $release = $releases ? reset($releases) : null;
        $reference = $release ? $releaser->getReleasePath($release) : null;

        $git->cloneAt($repository, $this->releasePath, $branch, $reference);
    }

    public function checkPaths(Shell $shell)
    {
        $shell->removePaths(['web/app_*.php', 'web/config.php'], $this->releasePath);

        $cacheDir = "{$this->releasePath}/app/cache";
        if ($shell->isDir($cacheDir)) {
            $shell->rm("$cacheDir/*");
        }
        $shell->chmod($cacheDir, 0775);
    }

    public function updateShared(Releaser $releaser)
    {
        $releaser->updateReleaseShares($this->releaseName, ['app/logs'], ['app/config/parameters.yml']);
    }

    public function installVendors(Composer $composer)
    {
        $composer->install($this->releasePath);
    }

    public function warmCache(Symfony $symfony)
    {
        $symfony->setConsolePath("{$this->releasePath}/app/console");

        $symfony->runCommand('cache:warmup');
    }

    public function installAssets(Symfony $symfony, $assetic_dump = false)
    {
        $symfony->runCommand('assets:install', [$this->releasePath . "/web"]);

        if ($assetic_dump) {
            $symfony->runCommand('assetic:dump');
        }
    }

    public function checkPermissions(Shell $shell)
    {
        $shell->isWritablePaths(['app/cache', 'app/logs'], $this->releasePath);
    }

    public function migrate(Symfony $symfony)
    {
        $symfony->runCommand('doctrine:migrations:migrate', [], ['allow-no-migration']);
    }

    public function release(Releaser $releaser)
    {
        $releaser->release($this->releaseName);
        $releaser->unlock();
    }

    public function cleanup(Releaser $releaser, $keep_releases = 5)
    {
        $releaser->cleanup($keep_releases);
    }
}
