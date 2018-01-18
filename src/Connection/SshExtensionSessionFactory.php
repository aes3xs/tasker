<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Connection;

class SshExtensionSessionFactory
{
    public function createForwardingAuthSession($host, $port, $login)
    {
        $configuration = new \Ssh\Configuration($host, $port);
        $authentication = new \Ssh\Authentication\Agent($login);
        return new \Ssh\Session($configuration, $authentication);
    }

    public function createPublicKeyAuthSession($host, $port, $login, $publicKey, $privateKey, $passphrase = null)
    {
        $configuration = new \Ssh\Configuration($host, $port);
        $authentication = new \Ssh\Authentication\PublicKeyFile($login, $publicKey, $privateKey, $passphrase);
        return new \Ssh\Session($configuration, $authentication);
    }

    public function createPasswordAuthSession($host, $port, $login, $password)
    {
        $configuration = new \Ssh\Configuration($host, $port);
        $authentication = new \Ssh\Authentication\Password($login, $password);
        return new \Ssh\Session($configuration, $authentication);
    }
}
