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

class ReleaserTest extends AbstractFunctionalTest
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

    public function testLock()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $releaser->lock($dir);
        $output = $this->getOutput();

        $this->assertFileExists("$dir/deploy.lock", $output);
    }

    public function testLockException()
    {
        $this->expectException(\RuntimeException::class);

        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $releaser->lock($dir);
        $releaser->lock($dir);
    }

    public function testUnlock()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        touch("$dir/deploy.lock");

        $releaser->unlock($dir);
        $output = $this->getOutput();

        $this->assertFileNotExists("$dir/deploy.lock", $output);
    }

    public function testPrepare()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $releaser->prepare($dir);
        $output = $this->getOutput();

        $this->assertDirectoryExists("$dir/releases", $output);
        $this->assertDirectoryExists("$dir/shared", $output);
    }

    public function testPrepareNoPathException()
    {
        $this->expectException(\RuntimeException::class);

        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $releaser->prepare("$dir/path");
    }

    public function testPrepareHasCurrentException()
    {
        $this->expectException(\RuntimeException::class);

        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        touch("$dir/current");

        $releaser->prepare($dir);
    }

    public function testCleanupBroken()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", date('Y-m-d H:i:s'));

        $releaser->cleanup($dir);
        $output = $this->getOutput();

        $this->assertDirectoryNotExists("$dir/releases/1", $output);
        $this->assertDirectoryExists("$dir/releases/2", $output);
    }

    public function testCleanupKeep()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        file_put_contents("$dir/releases/1/release.lock", '2017-01-03 00:00:00');
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", '2017-01-01 00:00:00');
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-02 00:00:00');

        $releaser->cleanup($dir, 2);
        $output = $this->getOutput();

        $this->assertDirectoryExists("$dir/releases/1", $output);
        $this->assertDirectoryNotExists("$dir/releases/2", $output);
        $this->assertDirectoryExists("$dir/releases/3", $output);
    }

    public function testCreate()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");

        $name = $releaser->create($dir, 1);
        $output = $this->getOutput();

        $this->assertEquals(1, $name, $output);
        $this->assertDirectoryExists("$dir/releases/1", $output);
    }

    public function testCreateGenerate()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");

        $name = $releaser->create($dir);
        $output = $this->getOutput();

        // Check generated value is current datetime in given format
        $date = \DateTime::createFromFormat('YmdHis', $name);
        $this->assertEquals($date->format('YmdHis'), $name, $output);
        $this->assertEquals(new \DateTime(), $date, $output, 5);

        $this->assertDirectoryExists("$dir/releases/$name", $output);
    }

    public function testRelease()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");

        $releaser->release($dir, 1);
        $output = $this->getOutput();

        $this->assertFileExists("$dir/releases/1/release.lock", $output);
        $data = file_get_contents("$dir/releases/1/release.lock");
        $this->assertEquals(new \DateTime(), \DateTime::createFromFormat('Y-m-d H:i:s', $data), $output, 5);

        $this->assertFileExists("$dir/current", $output);
        $this->assertTrue(is_link("$dir/current"), $output);
        $this->assertEquals(realpath("$dir/releases/1"), realpath("$dir/current"), $output);
    }

    public function testRollback()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        file_put_contents("$dir/releases/1/release.lock", '2017-01-01 00:00:00');
        mkdir("$dir/releases/2");
        touch("$dir/releases/2/release.lock");
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-02 00:00:00');
        symlink("$dir/releases/3", "$dir/current");

        $releaser->rollback($dir);
        $output = $this->getOutput();

        $this->assertDirectoryExists("$dir/releases/1", $output);
        $this->assertDirectoryExists("$dir/releases/2", $output);
        $this->assertDirectoryNotExists("$dir/releases/3", $output);
        $this->assertEquals(realpath("$dir/releases/1"), realpath("$dir/current"), $output);
    }

    public function testGetReleaseList()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", '');
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-01 00:00:00');

        $releases = $releaser->getReleaseList($dir);
        $output = $this->getOutput();

        $this->assertEquals(['2017-01-01 00:00:00' => '3'], $releases, $output);
    }

    public function testGetCurrentPath()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        symlink("$dir/releases/1", "$dir/current");

        $current = $releaser->getCurrentPath($dir);
        $output = $this->getOutput();

        $this->assertEquals("$dir/releases/1", $current, $output);
    }

    public function testGetCurrentPathNotExist()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $current = $releaser->getCurrentPath($dir);
        $output = $this->getOutput();

        $this->assertNull($current, $output);
    }

    public function testGetReleasePath()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");

        $release = $releaser->getReleasePath($dir, 1);
        $output = $this->getOutput();

        $this->assertEquals("$dir/releases/1", $release, $output);
    }

    public function testGetReleasePathNotExist()
    {
        $releaser = $this->getContainer()->get('releaser');
        $dir = $this->createTempDir();

        $release = $releaser->getReleasePath($dir, 1);
        $output = $this->getOutput();

        $this->assertNull($release, $output);
    }
}
