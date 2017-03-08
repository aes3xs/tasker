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
 * Helper service to interact with git repositories.
 */
class Git
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $gitPath;

    /**
     * @var bool
     */
    protected $isReferenceSupported;

    /**
     * @var string
     */
    protected $key;

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
     * @param $keyPath
     */
    public function setKey($keyPath)
    {
        $this->key = $keyPath;
    }

    /**
     * @param $path
     * @param $revision
     */
    public function checkout($path, $revision)
    {
        $git = $this->getGitPath();
        $this->shell->exec("cd $path && $git checkout $revision");
    }

    /**
     * @param $repository
     * @param $target
     * @param $at
     * @param null $reference
     */
    public function cloneAt($repository, $target, $at, $reference = null)
    {
        $git = $this->getGitPath();
        $at = "-b $at";

        if ($reference && !$this->isReferenceSupported()) {
            throw new \RuntimeException('Git --reference not supported');
        }

        if ($reference) {
            try {
                $this->shell->exec("$git clone $at --recursive -q --reference $reference --dissociate $repository $target");
            } catch (\Exception $e) {
                $this->shell->exec("$git clone $at --recursive -q $repository $target");
            }
        } else {
            $this->shell->exec("$git clone $at --recursive -q $repository $target");
        }
    }

    /**
     * @param $path
     * @param $count
     *
     * @return string
     */
    public function log($path, $count)
    {
        $count = intval($count);
        $git = $this->getGitPath();
        $this->shell->exec("cd $path && $git log -$count");
    }

    /**
     * @param $path
     */
    public function fetch($path)
    {
        $git = $this->getGitPath();
        $this->shell->exec("cd $path && $git fetch --prune");
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function getBranches($path)
    {
        $git = $this->getGitPath();
        $output = $this->shell->exec("cd $path && $git branch -r");

        $branches = [];
        if (preg_match_all('/^[\\s]*origin\\/([^\\s]+).*$/m', $output, $matches)) {
            $branches = $matches[1];
            $headIndex = array_search('HEAD', $branches);
            unset($branches[$headIndex]);
        }

        return $branches;
    }

    /**
     * @return string
     */
    protected function getGitPath()
    {
        if (null === $this->gitPath) {
            $gitPath = $this->shell->which('git');
            if (!$gitPath) {
                throw new \RuntimeException('Git not found');
            }

            $command = 'ssh';
            $command .= ' -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
            if ($this->key) {
                if (!$this->shell->exists($this->key)) {
                    throw new \RuntimeException('Key doesn\'t exist: ' . $this->key);
                }
                $command .= ' -i ' . $this->key;
            }

            $this->gitPath = sprintf('export GIT_SSH_COMMAND="%s"; %s', $command, $gitPath);
        }

        return $this->gitPath;
    }

    /**
     * @return bool
     */
    protected function isReferenceSupported()
    {
        if (null === $this->isReferenceSupported) {
            $git = $this->getGitPath();
            $gitVersion = $this->shell->exec("$git version");
            if (preg_match('/((\d+\.?)+)/', $gitVersion, $matches)) {
                $version = $matches[1];
            } else {
                $version = "1.0.0";
            }
            $this->isReferenceSupported = version_compare($version, '2.3', '>=');
        }

        return $this->isReferenceSupported;
    }
}
