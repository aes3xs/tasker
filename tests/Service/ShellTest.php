<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Service;

use Aes3xs\Yodler\Connection\LocalConnection;
use Aes3xs\Yodler\Connection\ProcessFactory;
use Aes3xs\Yodler\Service\Shell;
use Symfony\Component\Filesystem\Filesystem;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    protected function createTempDir()
    {
        $dir = stream_get_meta_data(tmpfile())['uri'];
        mkdir($dir);
        register_shutdown_function(function() use ($dir) {
            exec("rm -rf $dir");
        });
        return $dir;
    }

    protected function createShell()
    {
        return new Shell(new LocalConnection(new Filesystem(), new ProcessFactory()));
    }

    public function testExec()
    {
        $shell = $this->createShell();

        $result = $shell->exec("echo 'test'");

        $this->assertEquals('test', $result);
    }

    public function testLn()
    {
        $shell = $this->createShell();

        $dir = $this->createTempDir();
        mkdir("$dir/test");

        $shell->ln("$dir/test", "$dir/link");

        $this->assertFileExists("$dir/link");
        $this->assertEquals("$dir/test", realpath("$dir/link"));
    }
}
