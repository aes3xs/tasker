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

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function($class) {
    return class_exists($class, true);
});

use Aes3xs\Yodler\Kernel;
use Aes3xs\Yodler\Console\Application;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use Symfony\Component\ClassLoader\MapClassLoader;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$file = $input->getParameterOption(['--file'], 'deploy.yml');

$kernel = new Kernel($file);
$kernel->boot();

foreach ($kernel->getContainer()->getParameter('autoload') as $dir) {
    $map = ClassMapGenerator::createMap(new \RecursiveDirectoryIterator($dir));
    $map_loader = new MapClassLoader($map);
    $map_loader->register();
}

$application = new Application();
$application->setDispatcher($kernel->getContainer()->get('event_dispatcher'));
$application->setContainer($kernel->getContainer());
$application->run($input);
