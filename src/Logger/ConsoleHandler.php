<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Custom console handler.
 *
 * Based on \Symfony\Bridge\Monolog\Handler\ConsoleHandler
 */
class ConsoleHandler extends AbstractProcessingHandler
{
    protected $output;

    protected $verbosityLevelMap = [
        OutputInterface::VERBOSITY_QUIET        => Logger::ERROR,
        OutputInterface::VERBOSITY_NORMAL       => Logger::INFO,
        OutputInterface::VERBOSITY_VERBOSE      => Logger::DEBUG,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::DEBUG,
        OutputInterface::VERBOSITY_DEBUG        => Logger::DEBUG,
    ];

    /**
     * Constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        parent::__construct(Logger::DEBUG);

        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return $this->updateLevel() && parent::isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        return $this->updateLevel() && parent::handle($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->output->write((string) $record['formatted'], false, $this->output->getVerbosity());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter()
    {
        return new ConsoleFormatter(null, null, true, true);
    }

    protected function updateLevel()
    {
        $verbosity = $this->output->getVerbosity();
        if (isset($this->verbosityLevelMap[$verbosity])) {
            $this->setLevel($this->verbosityLevelMap[$verbosity]);
        } else {
            $this->setLevel(Logger::DEBUG);
        }
    }
}
