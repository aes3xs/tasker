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
     * @var string
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param CommanderInterface $commander
     */
    public function __construct(CommanderInterface $commander)
    {
        $this->commander = $commander;
    }

    public function setupUser($user, $sshForwarding = false)
    {
        $this->user = null;

        if ($sshForwarding) {
            if (!$this->which('setfacl')) {
                throw new \RuntimeException('ACL must be installed to share ssh forwarding. Run `sudo apt-get install acl`');
            }
            $sshAuthSock = $this->exec('echo "$SSH_AUTH_SOCK"');
            if ($sshAuthSock) {

                /**
                 * Share same ssh-agent between logged-in user and user switched to
                 * http://serverfault.com/a/698042
                 */
                $this->exec('setfacl -m ' . $user . ':x $(dirname "$SSH_AUTH_SOCK")');
                $this->exec('setfacl -m ' . $user . ':rwx "$SSH_AUTH_SOCK"');
            }
        }

        $this->user = $user;
    }

    /**
     * @param $command
     *
     * @return string
     */
    public function exec($command)
    {
        return $this->user ? $this->_execAs($command, $this->user) : $this->_exec($command);
    }

    /**
     * @param $command
     * @param $asUser
     * @return string
     */
    protected function _execAs($command, $asUser)
    {
        $command = strtr($command, ['"' => '\\"', '\\' => '\\\\']);
        $call = sprintf('sudo -HEiu %s bash -c "%s"', $asUser, $command);
        return $this->_exec($call);
    }

    /**
     * @param $command
     * @return string
     */
    protected function _exec($command)
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
        $origin = escapeshellarg($origin);
        $link = escapeshellarg($link);
        $relative = $relative ? '--relative' : '';
        $this->exec("ln -nfs $relative $origin $link");
    }

    /**
     * @param $path
     * @param $mode
     * @param bool $sudo
     * @param bool $recursive
     */
    public function chmod($path, $mode = 0755, $recursive = true, $sudo = false)
    {
        $path = escapeshellarg($path);
        $mode = sprintf("%04o", $mode);
        $recursive = $recursive ? '-R' : '';
        $sudo  = $sudo ? 'sudo' : '';
        $this->exec("$sudo chmod $recursive $mode $path");
    }

    /**
     * @param $path
     * @param $user
     * @param null $group
     * @param bool $sudo
     */
    public function chown($path, $user, $group = null, $sudo = false)
    {
        $path = escapeshellarg($path);
        $user = $group ? "$user:$group" : $user;
        $sudo  = $sudo ? 'sudo' : '';
        $this->exec("$sudo chown -RL $user $path");
    }

    /**
     * @param $path
     * @param bool $sudo
     */
    public function rm($path, $sudo = false)
    {
        $path = escapeshellarg($path);
        $sudo  = $sudo ? 'sudo' : '';
        $this->exec("$sudo rm -rf $path");
    }

    /**
     * @param $path
     * @param bool $recursive
     */
    public function mkdir($path, $recursive = true)
    {
        $path = escapeshellarg($path);
        $recursive = $recursive ? '-p' : '';
        $this->exec("mkdir $recursive $path");
    }

    /**
     * @param $path
     */
    public function touch($path)
    {
        $path = escapeshellarg($path);
        $this->exec("touch $path");
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function readlink($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("readlink $path");
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function realpath($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("realpath $path");
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function dirname($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("dirname $path");
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function ls($path)
    {
        $path = escapeshellarg($path);
        $result = $this->exec("ls -A $path");
        return $result ? explode(PHP_EOL, $result) : [];
    }

    /**
     * @param $command
     *
     * @return string|bool
     */
    public function which($command)
    {
        return $this->exec("which $command") ?: false;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function exists($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -e $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isFile($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -f $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isDir($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -d $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isLink($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -h $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isWritable($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -w $path ]; then echo 'true'; fi") === 'true';
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function isReadable($path)
    {
        $path = escapeshellarg($path);
        return $this->exec("if [ -r $path ]; then echo 'true'; fi") === 'true';
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

    public function copy($source, $target)
    {
        $source = escapeshellarg($source);
        $target = escapeshellarg($target);
        $this->exec("cp -r $source $target");
    }
}
