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
use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Commander factory implementation.
 */
class CommanderFactory
{
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
                        $configuration = new \Ssh\Configuration(
                            $connection->getHost(),
                            $connection->getPort()
                        );
                        $authentication = new \Ssh\Authentication\Agent(
                            $connection->getLogin()
                        );
                        $session = new \Ssh\Session($configuration, $authentication);
                        $commander = new SshExtensionCommander($session);
                        break;

                    case $connection->getPublicKey():
                        $configuration = new \Ssh\Configuration(
                            $connection->getHost(),
                            $connection->getPort()
                        );
                        $authentication = new \Ssh\Authentication\PublicKeyFile(
                            $connection->getLogin(),
                            $connection->getPublicKey(),
                            $connection->getPrivateKey(),
                            $connection->getPassphrase()
                        );
                        $session = new \Ssh\Session($configuration, $authentication);
                        $commander = new SshExtensionCommander($session);
                        break;

                    case $connection->getPassword():
                        $configuration = new \Ssh\Configuration(
                            $connection->getHost(),
                            $connection->getPort()
                        );
                        $authentication = new \Ssh\Authentication\Password(
                            $connection->getLogin(),
                            $connection->getPassword()
                        );
                        $session = new \Ssh\Session($configuration, $authentication);
                        $commander = new SshExtensionCommander($session);
                        break;

                    default:
                        throw new RuntimeException(sprintf('Auth method cannot be resolved for connection. Host: %s, public key? %s, forwarding? %s', $connection->getHost(), $connection->getPublicKey() ? 'yes' : 'no', $connection->isForwarding() ? 'yes' : 'no'));
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
     * @param HeapInterface $heap
     * @param LoggerInterface $logger
     *
     * @return ProxyCommander
     */
    public function createProxy(Connection $connection, HeapInterface $heap, LoggerInterface $logger)
    {
        return new ProxyCommander($connection, $this, $heap, $logger);
    }
}
