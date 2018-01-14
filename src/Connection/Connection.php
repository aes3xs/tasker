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

use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Resolver\ResourceResolver;
use Psr\Log\LoggerInterface;

/**
 * Connection is a configurable proxy which switches different connection types.
 */
class Connection implements ConnectionInterface
{
    /**
     * @var ConnectionParameters
     */
    protected $parameters;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param ConnectionFactory $connectionFactory
     * @param ResourceResolver $resourceResolver
     * @param LoggerInterface $logger
     */
    public function __construct(ConnectionFactory $connectionFactory, ResourceResolver $resourceResolver, LoggerInterface $logger)
    {
        $this->connectionFactory = $connectionFactory;
        $this->resourceResolver = $resourceResolver;
        $this->logger = $logger;
    }

    /**
     * Set connection parameters.
     *
     * @param ConnectionParameters $parameters
     */
    public function setParameters(ConnectionParameters $parameters)
    {
        $this->parameters = $parameters;

        $this->connection = null;
    }

    /**
     * Get connection parameters.
     *
     * @return ConnectionParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            if (!$this->parameters) {
                throw new RuntimeException("No connection parameters privided.");
            }

            $this->connection = $this->connectionFactory->create($this->parameters);
        }

        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function exec($command)
    {
        $command = $this->resourceResolver->resolveString($command);

        $this->logger->debug('> ' . $command);

        $output = $this->getConnection()->exec($command);

        $this->logger->debug('< ' . $command . ': ' . $output);

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function send($local, $remote)
    {
        $local = $this->resourceResolver->resolveString($local);
        $remote = $this->resourceResolver->resolveString($remote);

        $this->logger->debug('Send: ' . $local . ' to ' . $remote);

        $this->getConnection()->send($local, $remote);
    }

    /**
     * {@inheritdoc}
     */
    public function recv($remote, $local)
    {
        $remote = $this->resourceResolver->resolveString($remote);
        $local = $this->resourceResolver->resolveString($local);

        $this->logger->debug('Recv: ' . $remote . ' to ' . $local);

        $this->getConnection()->recv($remote, $local);
    }
}
