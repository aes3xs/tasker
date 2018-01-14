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
use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Connection factory.
 */
class ConnectionFactory
{
    /**
     * @var PhpSecLibClientFactory
     */
    protected $phpSecLibClientFactory;

    /**
     * Constructor.
     *
     * @param PhpSecLibClientFactory $phpSecLibClientFactory
     */
    public function __construct(PhpSecLibClientFactory $phpSecLibClientFactory)
    {
        $this->phpSecLibClientFactory = $phpSecLibClientFactory;
    }

    /**
     * Create connection from parameters.
     *
     * @param ConnectionParameters $connectionParameters
     *
     * @return ConnectionInterface
     *
     * @throws ConnectionAuthenticationException
     */
    public function create(ConnectionParameters $connectionParameters)
    {
        $filesystem = new Filesystem();
        $processFactory = new ProcessFactory();

        try {
            if ($connectionParameters->isLocalhost()) {
                $connection = new LocalConnection($filesystem, $processFactory);
            } else {

                switch (true) {
                    case $connectionParameters->isForwarding():
                        $session = $this->phpSecLibClientFactory->createForwardingAuthClient(
                            $connectionParameters->getHost(),
                            $connectionParameters->getPort(),
                            $connectionParameters->getLogin()
                        );
                        $connection = new PhpSecLibConnection($session);
                        break;

                    case $connectionParameters->getPublicKey():
                        $session = $this->phpSecLibClientFactory->createPublicKeyAuthClient(
                            $connectionParameters->getHost(),
                            $connectionParameters->getPort(),
                            $connectionParameters->getLogin(),
                            $connectionParameters->getPublicKeyContents(),
                            $connectionParameters->getPassphrase()
                        );
                        $connection = new PhpSecLibConnection($session);
                        break;

                    case $connectionParameters->getPassword():
                        $session = $this->phpSecLibClientFactory->createPasswordAuthClient(
                            $connectionParameters->getHost(),
                            $connectionParameters->getPort(),
                            $connectionParameters->getLogin(),
                            $connectionParameters->getPassword()
                        );
                        $connection = new PhpSecLibConnection($session);
                        break;

                    default:
                        throw new RuntimeException(sprintf('Auth method cannot be resolved for connection. Host: %s, public key? %s, forwarding? %s', $connectionParameters->getHost(), $connectionParameters->getPublicKey() ? 'yes' : 'no', $connectionParameters->isForwarding() ? 'yes' : 'no'));
                }
            }
        } catch (ConnectionAuthenticationException $e) {
            $e->setConnectionParameters($connectionParameters);
            throw $e;
        }

        return $connection;
    }
}
