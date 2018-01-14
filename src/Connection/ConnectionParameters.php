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

use Aes3xs\Yodler\Exception\FileReadException;

/**
 * ConnectionParameters.
 */
class ConnectionParameters
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $passphrase;

    /**
     * @var bool
     */
    protected $forwarding;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return ConnectionParameters
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocalhost()
    {
        return in_array($this->getHost(), [null, 'localhost', '127.0.0.1']);
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return ConnectionParameters
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return ConnectionParameters
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return ConnectionParameters
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getPublicKeyContents()
    {
        if ($this->publicKey && file_exists($this->publicKey)) {
            $keyContent = file_get_contents($this->publicKey);
            if ($keyContent === false) {
                throw new FileReadException($this->publicKey);
            }
            return $keyContent;
        }

        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return ConnectionParameters
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getPrivateKeyContents()
    {
        if ($this->privateKey && file_exists($this->privateKey)) {
            $keyContent = file_get_contents($this->privateKey);
            if ($keyContent === false) {
                throw new FileReadException($this->privateKey);
            }
            return $keyContent;
        }

        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     *
     * @return ConnectionParameters
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * @param string $passphrase
     *
     * @return ConnectionParameters
     */
    public function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isForwarding()
    {
        return $this->forwarding;
    }

    /**
     * @param boolean $forwarding
     *
     * @return ConnectionParameters
     */
    public function setForwarding($forwarding)
    {
        $this->forwarding = $forwarding;

        return $this;
    }
}
