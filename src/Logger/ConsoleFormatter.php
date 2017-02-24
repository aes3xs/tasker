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

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * Custom console formatter.
 */
class ConsoleFormatter extends LineFormatter
{
    const SIMPLE_DATE = 'H:i:s';
    const SIMPLE_FORMAT = "%start_tag%[%datetime%] %level_name% %channel%:%end_tag% %message_start_tag%%message% %context% %extra%%message_end_tag%\n";

    /**
     * @var HeapInterface
     */
    protected $heap;

    /**
     * @param HeapInterface $heap
     */
    public function setHeap(HeapInterface $heap)
    {
        $this->heap = $heap;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record['channel'] = $this->getChannelName($this->heap->get('deployContext'));

        if ($record['level'] >= Logger::ERROR) {
            $record['start_tag'] = '<error>';
            $record['end_tag'] = '</error>';
        } elseif ($record['level'] >= Logger::NOTICE) {
            $record['start_tag'] = '<comment>';
            $record['end_tag'] = '</comment>';
        } elseif ($record['level'] >= Logger::INFO) {
            $record['start_tag'] = '<info>';
            $record['end_tag'] = '</info>';
        } else {
            $record['start_tag'] = "\033[1;30m";
            $record['end_tag'] = "\033[0m";
        }

        if ($record['level'] >= Logger::ERROR) {
            $record['message_start_tag'] = '<error>';
            $record['message_end_tag'] = '</error>';
        } elseif ($record['level'] === Logger::DEBUG) {
            $record['message_start_tag'] = "\033[1;30m";
            $record['message_end_tag'] = "\033[0m";
        } else {
            $record['message_start_tag'] = '';
            $record['message_end_tag'] = '';
        }

        return parent::format($record);
    }

    /**
     * @param $data
     *
     * @return mixed|string
     */
    protected function convertToString($data)
    {
        if (is_scalar($data)) {
            return parent::convertToString($data);
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = parent::convertToString($item);
            }
            return implode(PHP_EOL, $result);
        }

        return var_export($this->normalize($data), true);
    }

    /**
     * @param DeployContextInterface $deployContext
     * @return string
     */
    protected function getChannelName(DeployContextInterface $deployContext)
    {
        return sprintf('%s@%s',
            $deployContext->getScenario()->getName(),
            $deployContext->getConnection()->getName()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeException($e)
    {
        return $this->formatException($e);
    }

    /**
     * @param \Exception $e
     *
     * @return string
     */
    protected function formatException(\Exception $e)
    {
        $result = [
            sprintf('%s: %s', get_class($e), $e->getMessage())
        ];

        $trace = $e->getTrace();

        $file = $e->getFile();
        $line = $e->getLine();

        foreach ($trace as $i => $item) {

            if (isset($item['class'])) {
                preg_match('/([A-Za-z0-9])+$/', $item['class'], $matches);
                $item['class'] = $matches[0];
            }
            $line = is_null($line) ? '' : ":$line";

            $call = sprintf("%s%s%s",
                isset($item['class']) ? $item['class'] : '',
                isset($item['class']) && isset($item['function']) ? '->' : ' ',
                isset($item['function']) ? $item['function'] . "()" : '(main)'
            );

            $result[] = sprintf("#%s %s %s", $i + 1, $call, $file . $line);

            $file = isset($item['file']) ? $item['file'] : 'internal';
            $line = isset($item['file']) && isset($item['line']) && $item['line'] ? $item['line'] : null;
        }

        return join(PHP_EOL, $result);
    }
}
