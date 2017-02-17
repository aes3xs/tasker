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

/**
 * User definition implementation
 */
class User implements UserInterface
{
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
    protected $key;

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
     *
     * @param string $login
     * @param string $password
     * @param string $key
     * @param string $passphrase
     * @param bool $forwarding
     */
    public function __construct($login, $password = null, $key = null, $passphrase = null, $forwarding = false)
    {
        $this->login = $login;
        $this->password = $password;
        $this->key = $key;
        $this->passphrase = $passphrase;
        $this->forwarding = $forwarding;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * {@inheritdoc}
     */
    public function getForwarding()
    {
        return $this->forwarding;
    }
}
