<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Connection;

use Aes3xs\Yodler\Exception\ConnectionAuthenticationException;

class PhpSecLibClientFactory
{
    public function createForwardingAuthClient($host, $port, $login)
    {
        $client = new \phpseclib\Net\SFTP($host, $port);

        $agent = new \phpseclib\System\SSH\Agent();
        $agent->startSSHForwarding(null);

        if (!$client->login($login, $agent)) {
            throw new ConnectionAuthenticationException();
        }

        return $client;
    }

    public function createPublicKeyAuthClient($host, $port, $login, $publicKey, $passphrase = null)
    {
        $client = new \phpseclib\Net\SFTP($host, $port);

        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->setPassword($passphrase);
        $rsa->loadKey($publicKey);

        if (!$client->login($login, $rsa)) {
            throw new ConnectionAuthenticationException();
        }

        return $client;
    }

    public function createPasswordAuthClient($host, $port, $login, $password)
    {
        $client = new \phpseclib\Net\SFTP($host, $port);

        if (!$client->login($login, $password)) {
            throw new ConnectionAuthenticationException();
        }

        return $client;
    }
}
