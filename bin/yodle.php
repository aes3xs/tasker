<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoloadFiles = array(
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
);

$autoloader = false;
foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        $autoloader = true;
    }
}

if (!$autoloader) {
    die('vendor/autoload.php could not be found.');
}

use Aes3xs\Yodler\Kernel;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$file = $input->getParameterOption(['--file'], 'deploy.yml');

$kernel = new Kernel($file);
$kernel->boot();
$kernel->getContainer()->get('application')->run($input);
