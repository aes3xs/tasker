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

use Aes3xs\Yodler\Commander\CommanderInterface;

/**
 * Helper service to provide shortcuts for frequently used shell commands.
 */
class Shell
{
    /**
     * @var CommanderInterface
     */
    protected $commander;

    /**
     * @var bool
     */
    protected $lnRelativeSupported;

    /**
     * Constructor.
     *
     * @param CommanderInterface $commander
     */
    public function __construct(CommanderInterface $commander)
    {
        $this->commander = $commander;
    }

    /**
     * @param $command
     *
     * @return string|array
     */
    public function exec($command)
    {
        $result = $this->commander->exec($command);
        if (false !== strpos($result, 'stdin: is not a tty')) {
            throw new \RuntimeException('stdin: is not a tty');
        }
        return $result;
    }

    /**
     * @param $origin
     * @param $link
     * @param bool $relative
     */
    public function ln($origin, $link, $relative = true)
    {
        if ($relative && $this->lnRelativeSupported === null) {
            // Check if target system supports relative symlink.
            $this->lnRelativeSupported = $this->exec('if [[ "$(man ln)" =~ "--relative" ]]; then echo "true"; fi') === 'true';
        }
        $relative = $this->lnRelativeSupported === true ? '--relative' : '';
        $this->exec("ln -nfs $relative $origin $link");
    }

    /**
     * @param $path
     * @param $mode
     * @param bool $sudo
     * @param bool $recursive
     */
    public function chmod($path, $mode, $sudo = true, $recursive = true)
    {
        $path = is_array($path) ? implode(' ', $path) : $path;
        $recursive = $recursive ? '-R' : '';
        $this->exec("$sudo chmod $recursive $mode $path");
    }

    /**
     * @param $path
     * @param $user
     * @param null $group
     * @param bool $sudo
     */
    public function chown($path, $user, $group = null, $sudo = true)
    {
        $path = is_array($path) ? implode(' ', $path) : $path;
        $user = $group ? "$user:$group" : $user;
        $this->exec("$sudo chown -RL $user $path");
    }

    /**
     * @param $path
     * @param bool $sudo
     */
    public function rm($path, $sudo = true)
    {
        $paths = !is_array($path) ? [$path] : $path;
        $sudo  = $sudo ? 'sudo' : '';
        foreach ($paths as $path) {
            $this->exec("$sudo rm -rf $path");
        }
    }

    /**
     * @param $path
     * @param bool $recursive
     */
    public function mkdir($path, $recursive = true)
    {
        $recursive = $recursive ? '-p' : '';
        $this->exec("mkdir $recursive $path");
    }

    /**
     * @param $path
     */
    public function touch($path)
    {
        $this->commander->exec("touch $path");
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function readlink($path)
    {
        return $this->exec("readlink $path");
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function realpath($path)
    {
        return $this->exec("realpath $path");
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function ls($path)
    {
        $result = $this->exec("ls -A $path");
        return $result ? explode(PHP_EOL, $result) : [];
    }

    /**
     * @param $command
     *
     * @return bool
     */
    public function commandExist($command)
    {
        return $this->exec("if hash $command 2>/dev/null; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return $this->exec("if [ -e $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isFile($path)
    {
        return $this->exec("if [ -f $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isDir($path)
    {
        return $this->exec("if [ -d $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isLink($path)
    {
        return $this->exec("if [ -h $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $file
     * @param $data
     */
    public function write($file, $data)
    {
        $tmp = tmpfile();
        fwrite($tmp, $data);
        $this->commander->send(stream_get_meta_data($tmp)['uri'], $file);
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function read($file)
    {
        $tmp = tmpfile();
        $tmp_file = stream_get_meta_data($tmp)['uri'];
        $this->commander->recv($file, $tmp_file);
        $filesize = filesize($tmp_file);
        return $filesize ? fread($tmp, $filesize) : '';
    }
}
