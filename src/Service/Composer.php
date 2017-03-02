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
 * Helper service to use composer.
 */
class Composer
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $phpPath;

    /**
     * @var string
     */
    protected $composerPath;

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
     * @param $path
     * @param bool $dev
     */
    public function install($path, $dev = false)
    {
        $composer = $this->getComposer($path);

        $dev = $dev ? '' : '--no-dev';
        $options = "--verbose --prefer-dist --no-progress --no-interaction $dev --optimize-autoloader";

        $this->shell->exec("cd $path && $composer install $options");
    }

    /**
     * @param $path
     * @param bool $dev
     */
    public function update($path, $dev = false)
    {
        $composer = $this->getComposer($path);

        $dev = $dev ? '' : '--no-dev';
        $options = "--verbose --prefer-dist --no-progress --no-interaction $dev --optimize-autoloader";

        $this->shell->exec("cd $path && $composer update $options");
    }

    /**
     * @param $path
     * @return string
     */
    protected function download($path)
    {
        $php = $this->getPhpPath();
        $this->shell->exec("cd $path && curl -sS https://getcomposer.org/installer | $php");
        return "$path/composer.phar";
    }

    /**
     * @param $path
     * @return string
     */
    protected function getComposer($path)
    {
        $php = $this->getPhpPath();
        return $this->shell->which('composer') ?: "$php " . $this->download($path);
    }

    /**
     * @return string
     */
    protected function getPhpPath()
    {
        if (null === $this->phpPath) {
            $phpPath = $this->shell->which('php');
            if (!$phpPath) {
                throw new \RuntimeException('PHP not found');
            }
            $this->phpPath = $phpPath;
        }

        return $this->phpPath;
    }
}
