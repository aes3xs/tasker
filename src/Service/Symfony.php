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
 * Helper service to manage symfony project.
 */
class Symfony
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
    protected $consolePath;

    /**
     * @var string
     */
    protected $env = 'prod';

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var bool
     */
    protected $interaction = false;

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
     * Set console path.
     *
     * @param $consolePath
     */
    public function setConsolePath($consolePath)
    {
        $this->consolePath = $consolePath;
    }

    /**
     * Get console path.
     *
     * @return string
     */
    public function getConsolePath()
    {
        if (null === $this->consolePath) {
            throw new \RuntimeException('Console path is not defined');
        }

        return $this->consolePath;
    }

    /**
     * @param $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param $interaction
     */
    public function setInteraction($interaction)
    {
        $this->interaction = $interaction;
    }

    public function runCommand($command, $arguments = [], $options = [])
    {
        foreach ($options as $i => $value) {
            if (is_numeric($i)) {
                $options[$value] = null;
                unset($options[$i]);
            }
        }

        $php = $this->getPhpPath();
        $console = $this->getConsolePath();

        $predefinedOptions = ['env' => $this->env];
        if (!$this->debug) {
            $predefinedOptions['no-debug'] = null;
        }
        if (!$this->interaction) {
            $predefinedOptions['no-interaction'] = null;
        }

        $options = $options + $predefinedOptions;

        $argumentLine = implode(' ', $arguments);
        $optionLine = '';
        foreach ($options as $name => $value) {
            $name = false === strpos($name, '--') ? " --$name" : " $name"; // Add preceding --
            $value = null === $value ? '=' . $value : '';
            $optionLine .= $name . $value;
        }

        return $this->shell->exec("$php $console $command $argumentLine $optionLine");
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
