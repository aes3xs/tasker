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
 * Helper service to manage symfony project.
 */
class Symfony
{
    const DEFAULT_OPTIONS = [
        'env' => 'prod',
        'no-interaction',
        'no-debug',
    ];

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * Constructor.
     *
     * @param Shell $shell
     */
    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function runCommand($console, $command, $arguments = [], $options = self::DEFAULT_OPTIONS)
    {
        $argumentLine = implode(' ', $arguments);
        $optionLine = '';
        foreach ($options as $name => $value) {
            if (is_numeric($name)) {
                $name = $value;
                $value = null;
            }
            $name = strpos($name, '--') !== 0 ? ' --' : ''; // Add preceding --
            $value = $value ? '=' . $value : '';
            $optionLine .= $name . $value;
        }

        return $this->shell->exec("$console $command $argumentLine $optionLine");
    }
}
