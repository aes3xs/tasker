<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Service;

/**
 * Helper service to manage releases.
 */
class Releaser
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $deployPath;

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
     * Set deploy path.
     *
     * @param $path
     */
    public function setDeployPath($path)
    {
        $this->deployPath = $path;
    }

    /**
     * Get deploy path.
     *
     * @return string
     */
    public function getDeployPath()
    {
        if (null === $this->deployPath) {
            throw new \RuntimeException('Deploy path is not defined');
        }

        return $this->deployPath;
    }

    /**
     * Lock deploy.
     */
    public function lock()
    {
        $path = $this->getDeployPath();

        if ($this->isLocked()) {
            throw new \RuntimeException("Deploy locked. Unlock to proceed.");
        } else {
            $this->shell->touch("$path/deploy.lock");
        }
    }

    /**
     * Check if deploy is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        $path = $this->getDeployPath();

        return $this->shell->exists("$path/deploy.lock");
    }

    /**
     * Unlock deploy.
     */
    public function unlock()
    {
        $path = $this->getDeployPath();

        $this->shell->rm("$path/deploy.lock");
    }

    /**
     * Prepare directory structure for deploy.
     */
    public function prepare()
    {
        $path = $this->getDeployPath();

        if (!$this->shell->isDir($path)) {
            throw new \RuntimeException('Not a directory: ' . $path);
        }
        if ($this->shell->exists("$path/current") && !$this->shell->isLink("$path/current")) {
            throw new \RuntimeException('Not a link: ' . "$path/current");
        }
        if (!$this->shell->isDir("$path/releases")) {
            $this->shell->mkdir("$path/releases");
        }
        if (!$this->shell->isDir("$path/shared")) {
            $this->shell->mkdir("$path/shared");
        }
    }

    /**
     * Delete old and broken releases.
     *
     * @param int $keep
     */
    public function cleanup($keep = null)
    {
        $path = $this->getDeployPath();

        $releases = $this->getReleaseList();

        $list = $this->shell->ls("$path/releases");

        $broken = array_diff($list, $releases);
        foreach ($broken as $release) {
            $this->shell->rm("$path/releases/$release");
        }

        if (null === $keep) {
            return;
        }

        $outdated = array_slice($releases, $keep);
        foreach ($outdated as $release) {
            $this->shell->rm("$path/releases/$release");
        }
    }

    /**
     * Create release.
     *
     * @param $name
     *
     * @return string
     */
    public function create($name = null)
    {
        $path = $this->getDeployPath();

        $name = $name ?: date('YmdHis');

        if ($this->shell->exists("$path/releases/$name")) {
            throw new \RuntimeException('Path already exists: ' . "$path/releases/$name");
        }

        $this->shell->mkdir("$path/releases/$name");

        return $name;
    }

    /**
     * Make a release.
     *
     * @param $name
     */
    public function release($name)
    {
        $path = $this->getDeployPath();

        $this->shell->ln("$path/releases/$name", "$path/current");
        $date = date('Y-m-d H:i:s');
        $this->shell->exec("echo -n '$date' > $path/releases/$name/release.lock");
    }

    /**
     * Directly link release.
     *
     * @param $name
     */
    public function link($name)
    {
        $path = $this->getDeployPath();

        $this->shell->ln("$path/releases/$name", "$path/current");
    }

    /**
     * Rollback to previous release.
     *
     * @return string
     */
    public function rollback()
    {
        $path = $this->getDeployPath();

        $releases = $this->getReleaseList();
        krsort($releases);

        $currentRelease = array_shift($releases);
        $previousRelease = reset($releases);

        if ($previousRelease) {
            $this->shell->ln("$path/releases/$previousRelease", "$path/current");
            $this->shell->rm("$path/releases/$currentRelease");
        } else {
            throw new \RuntimeException('No available release to revert to');
        }

        return $previousRelease;
    }

    /**
     * Update release shares.
     *
     * @param $name
     * @param array $sharedDirs
     * @param array $sharedFiles
     */
    public function updateReleaseShares($name, $sharedDirs = [], $sharedFiles = [])
    {
        $path = $this->getDeployPath();

        foreach ($sharedDirs as $a) {
            foreach ($sharedDirs as $b) {
                if ($a !== $b && strpos($a, $b) === 0) {
                    throw new \RuntimeException("Nested shared directories: $a, $b");
                }
            }
        }

        $release = $this->getReleasePath($name);

        foreach ($sharedDirs as $dir) {

            if (!$this->shell->exists("$path/shared/$dir")) {
                $this->shell->mkdir("$path/shared/$dir");

                // Initialize share from source release
                if ($this->shell->isDir("$release/$dir")) {
                    $this->shell->copy("$release/$dir", "$path/shared/" . dirname($dir));
                }
            }

            if (!$this->shell->isDir("$path/shared/$dir")) {
                throw new \RuntimeException('Not a directory: ' . "$path/shared/$dir");
            }

            $this->shell->rm("$release/$dir");
            $this->shell->mkdir(dirname("$release/$dir"));
            $this->shell->ln("$path/shared/$dir", "$release/$dir");
        }

        foreach ($sharedFiles as $file) {

            $this->shell->mkdir(dirname("$path/shared/$file"));

            if (!$this->shell->exists("$path/shared/$file")) {
                $this->shell->touch("$path/shared/$file");

                // Initialize share from source release
                if ($this->shell->isFile("$release/$file")) {
                    $this->shell->copy("$release/$file", "$path/shared/$file");
                }
            }

            if (!$this->shell->isFile("$path/shared/$file")) {
                throw new \RuntimeException('Not a file: ' . "$path/shared/$file");
            }

            $this->shell->rm("$release/$file");
            $this->shell->mkdir(dirname("$release/$file"));
            $this->shell->ln("$path/shared/$file", "$release/$file");
        }
    }

    /**
     * Get release list.
     *
     * @return array
     */
    public function getReleaseList()
    {
        $path = $this->getDeployPath();

        $path = rtrim($path, '/');

        if (!$this->shell->isDir("$path/releases")) {
            return [];
        }

        $output = $this->shell->exec("find $path/releases -maxdepth 2 -mindepth 2 -path \"*/*/release.lock\"");

        $list = [];
        $rplc = ['/' => '\/', '.' => '\.'];
        $pattern = strtr("$path/releases/", $rplc) . '(.+)' . strtr('/release.lock', $rplc);
        if (preg_match_all("~$pattern~", $output, $matches)) {
            $list = $matches[1];
        }

        $releases = [];
        foreach ($list as $name) {
            $date = $this->shell->read("$path/releases/$name/release.lock");
            if ($date) {
                $releases[$date] = $name;
            }
        }

        krsort($releases);

        return $releases;
    }

    /**
     * Get current release path.
     *
     * @return string|null
     */
    public function getCurrentPath()
    {
        $path = $this->getDeployPath();

        return $this->shell->exists("$path/current") ? $this->shell->realpath("$path/current") : null;
    }

    /**
     * Get current release name.
     *
     * @return string|null
     */
    public function getCurrentRelease()
    {
        $path = $this->getDeployPath();

        $current = $this->getCurrentPath();

        $rplc = ['/' => '\/', '.' => '\.'];
        $pattern = strtr("$path/releases/", $rplc) . '(.+)';
        if (preg_match("~$pattern~", $current, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get release path by name.
     *
     * @param $name
     *
     * @return string
     */
    public function getReleasePath($name)
    {
        $path = $this->getDeployPath();

        return $this->shell->exists("$path/releases/$name") ? $this->shell->realpath("$path/releases/$name") : null;
    }
}
