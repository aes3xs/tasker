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

use Aes3xs\Yodler\Commander\LocalCommander;
use Aes3xs\Yodler\Common\ProcessFactory;
use Aes3xs\Yodler\Service\Releaser;
use Aes3xs\Yodler\Service\Shell;
use Symfony\Component\Filesystem\Filesystem;

class ReleaserTest extends \PHPUnit_Framework_TestCase
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

    protected function createReleaser($dir)
    {
        $shell = new Shell(new LocalCommander(new Filesystem(), new ProcessFactory()));
        
        $releaser = new Releaser($shell);
        $releaser->setDeployPath($dir);
        
        return $releaser;
    }

    public function testLock()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);
        
        $releaser->lock();
        
        $this->assertFileExists("$dir/deploy.lock");
    }

    public function testLockException()
    {
        $this->expectException(\Exception::class);

        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        $releaser->lock();
        $releaser->lock();
    }

    public function testUnlock()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);
        
        touch("$dir/deploy.lock");

        $releaser->unlock();

        $this->assertFileNotExists("$dir/deploy.lock");
    }

    public function testPrepare()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        $releaser->prepare();

        $this->assertDirectoryExists("$dir/releases");
        $this->assertDirectoryExists("$dir/shared");
    }

    public function testPrepareNoPathException()
    {
        $this->expectException(\Exception::class);

        $dir = $this->createTempDir();
        $releaser = $this->createReleaser("$dir/path");

        $releaser->prepare();
    }

    public function testPrepareHasCurrentException()
    {
        $this->expectException(\Exception::class);

        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);
        
        touch("$dir/current");

        $releaser->prepare();
    }

    public function testCleanupBroken()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);
        
        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", date('Y-m-d H:i:s'));

        $releaser->cleanup();

        $this->assertDirectoryNotExists("$dir/releases/1");
        $this->assertDirectoryExists("$dir/releases/2");
    }

    public function testCleanupKeep()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        file_put_contents("$dir/releases/1/release.lock", '2017-01-03 00:00:00');
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", '2017-01-01 00:00:00');
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-02 00:00:00');

        $releaser->cleanup(2);

        $this->assertDirectoryExists("$dir/releases/1");
        $this->assertDirectoryNotExists("$dir/releases/2");
        $this->assertDirectoryExists("$dir/releases/3");
    }

    public function testCreate()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");

        $name = $releaser->create(1);

        $this->assertEquals(1, $name);
        $this->assertDirectoryExists("$dir/releases/1");
    }

    public function testCreateGenerate()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");

        $name = $releaser->create();

        // Check generated value is current datetime in given format
        $date = \DateTime::createFromFormat('YmdHis', $name);
        $this->assertInstanceOf(\DateTime::class, $date);
        $this->assertEquals($date->format('YmdHis'), $name);
        $this->assertEquals(new \DateTime(), $date, 5);

        $this->assertDirectoryExists("$dir/releases/$name");
    }

    public function testRelease()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");

        $releaser->release(1);

        $this->assertFileExists("$dir/releases/1/release.lock");
        $data = file_get_contents("$dir/releases/1/release.lock");
        $this->assertEquals(new \DateTime(), \DateTime::createFromFormat('Y-m-d H:i:s', $data), 5);

        $this->assertFileExists("$dir/current");
        $this->assertTrue(is_link("$dir/current"));
        $this->assertEquals(realpath("$dir/releases/1"), realpath("$dir/current"));
    }

    public function testRollback()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        file_put_contents("$dir/releases/1/release.lock", '2017-01-01 00:00:00');
        mkdir("$dir/releases/2");
        touch("$dir/releases/2/release.lock");
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-02 00:00:00');
        symlink("$dir/releases/3", "$dir/current");

        $releaser->rollback();

        $this->assertDirectoryExists("$dir/releases/1");
        $this->assertDirectoryExists("$dir/releases/2");
        $this->assertDirectoryNotExists("$dir/releases/3");
        $this->assertEquals(realpath("$dir/releases/1"), realpath("$dir/current"));
    }

    public function testGetReleaseList()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        mkdir("$dir/releases/2");
        file_put_contents("$dir/releases/2/release.lock", '');
        mkdir("$dir/releases/3");
        file_put_contents("$dir/releases/3/release.lock", '2017-01-01 00:00:00');

        $releases = $releaser->getReleaseList();

        $this->assertEquals(['2017-01-01 00:00:00' => '3'], $releases);
    }

    public function testGetCurrentPath()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");
        symlink("$dir/releases/1", "$dir/current");

        $current = $releaser->getCurrentPath();

        $this->assertEquals("$dir/releases/1", $current);
    }

    public function testGetCurrentPathNotExist()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        $current = $releaser->getCurrentPath();

        $this->assertNull($current);
    }

    public function testGetReleasePath()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        mkdir("$dir/releases");
        mkdir("$dir/releases/1");

        $release = $releaser->getReleasePath(1);

        $this->assertEquals("$dir/releases/1", $release);
    }

    public function testGetReleasePathNotExist()
    {
        $dir = $this->createTempDir();
        $releaser = $this->createReleaser($dir);

        $release = $releaser->getReleasePath(1);

        $this->assertNull($release);
    }
}
