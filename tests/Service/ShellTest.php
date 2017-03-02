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

use Aes3xs\Yodler\Tests\AbstractFunctionalTest;

class ShellTest extends AbstractFunctionalTest
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

    public function testExec()
    {
        $shell = $this->getContainer()->get('shell');

        $result = $shell->exec("echo 'test'");
        $output = $this->getOutput();

        $this->assertEquals('test', $result, $output);
    }

    public function testLn()
    {
        $shell = $this->getContainer()->get('shell');
        $dir = $this->createTempDir();
        mkdir("$dir/test");

        $shell->ln("$dir/test", "$dir/link");
        $output = $this->getOutput();

        $this->assertFileExists("$dir/link", $output);
        $this->assertEquals("$dir/test", realpath("$dir/link"), $output);
    }
}
