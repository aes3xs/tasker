<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Commander;

use Aes3xs\Yodler\Common\ProcessFactory;
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Exception\CommanderAuthenticationException;
use Aes3xs\Yodler\Exception\RuntimeException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Commander factory implementation.
 */
class CommanderFactory
{
    /**
     * @var SftpFactory
     */
    protected $sftpFactory;

    /**
     * Constructor.
     *
     * @param SftpFactory $sftpFactory
     */
    public function __construct(SftpFactory $sftpFactory)
    {
        $this->sftpFactory = $sftpFactory;
    }

    /**
     * Create commander from connection definition.
     *
     * @param Connection $connection
     *
     * @return CommanderInterface
     *
     * @throws CommanderAuthenticationException
     */
    public function create(Connection $connection)
    {
        $filesystem = new Filesystem();
        $processFactory = new ProcessFactory();

        $isLocalhost = in_array($connection->getHost(), [null, 'localhost', '127.0.0.1']);

        try {
            if ($isLocalhost) {
                $commander = new LocalCommander($filesystem, $processFactory);
            } else {
                switch (true) {
                    case $connection->isForwarding():
                        $sftp = $this->createPhpSecLibForwardingClient(
                            $connection->getHost(),
                            $connection->getPort(),
                            $connection->getLogin()
                        );
                        $commander = new PhpSecLibCommander($sftp);
                        break;

                    case $connection->getKey():
                        $sftp = $this->createPhpSecLibKeyClient(
                            $connection->getHost(),
                            $connection->getPort(),
                            $connection->getLogin(),
                            $connection->getKey(),
                            $connection->getPassphrase()
                        );
                        $commander = new PhpSecLibCommander($sftp);
                        break;

                    case $connection->getPassword():
                        $sftp = $this->createPhpSecLibPasswordClient(
                            $connection->getHost(),
                            $connection->getPort(),
                            $connection->getLogin(),
                            $connection->getPassword()
                        );
                        $commander = new PhpSecLibCommander($sftp);
                        break;

                    default:
                        throw new RuntimeException(sprintf('Auth method cannot be resolved for connection'));
                }
            }
        } catch (CommanderAuthenticationException $e) {
            $e->setConnection($connection);
            throw $e;
        }

        return $commander;
    }

    /**
     * @param Connection $connection
     *
     * @return LazyCommander
     */
    public function createLazy(Connection $connection)
    {
        return new LazyCommander($connection, $this);
    }

    /**
     * @param $host
     * @param $port
     * @param $login
     * @param $password
     *
     * @return SFTP
     */
    protected function createPhpSecLibPasswordClient($host, $port, $login, $password)
    {
        $sftp = $this->sftpFactory->create($host, $port);

        if (!$sftp->login($login, $password)) {
            throw new CommanderAuthenticationException();
        }

        return $sftp;
    }

    /**
     * @param $host
     * @param $port
     * @param $login
     * @param $key
     * @param $passphrase
     *
     * @return SFTP
     */
    protected function createPhpSecLibKeyClient($host, $port, $login, $key, $passphrase)
    {
        $sftp = $this->sftpFactory->create($host, $port);
        $rsa = new RSA();
        $rsa->setPassword($passphrase);
        $rsa->loadKey($key);

        if (!$sftp->login($login, $rsa)) {
            throw new CommanderAuthenticationException();
        }

        return $sftp;
    }

    /**
     * @param $host
     * @param $port
     * @param $login
     *
     * @return SFTP
     */
    protected function createPhpSecLibForwardingClient($host, $port, $login)
    {
        $sftp = $this->sftpFactory->create($host, $port);
        $agent = new Agent();
        $agent->startSSHForwarding(null);

        if (!$sftp->login($login, $agent)) {
            throw new CommanderAuthenticationException();
        }

        return $sftp;
    }
}
