<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Action;

use Aes3xs\Yodler\Heap\HeapInterface;
use Psr\Log\LoggerInterface;

/**
 * Message action is used to pass message to logger.
 */
class MessageAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $level;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param $message
     * @param $level
     * @param LoggerInterface $logger
     */
    public function __construct($message, $level, LoggerInterface $logger)
    {
        $this->message = $message;
        $this->level = $level;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ucfirst($this->level);
    }

    /**
     * {@inheritdoc}
     */
    public function skip(HeapInterface $heap)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(HeapInterface $heap)
    {
        $this->logger->log($this->level, $heap->resolveString($this->message));
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [];
    }
}
