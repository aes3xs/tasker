<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Terminal;

/**
 * Custom console formatter.
 */
class ConsoleFormatter extends LineFormatter
{
    /**
     * @var int
     */
    protected $lineLength;

    const SIMPLE_DATE = 'H:i:s';
    const SIMPLE_FORMAT = "%head%[%datetime%] %level_name% %channel%:%/head% %body%%message%%/body% %aux%%context%%/aux% %aux%%extra%%/aux%\n";
    const MAX_LINE_LENGTH = 120;

    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);

        /* Simplified realization from \Symfony\Component\Console\Style\SymfonyStyle */
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $width = (new Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = min($width - (int) (DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record['head'] = "";
        $record['/head'] = "";
        $record['body'] = "";
        $record['/body'] = "";
        $record['aux'] = "";
        $record['/aux'] = "";

        if ($record['level'] >= Logger::ERROR) {
            $record['head'] = "\033[1;31m";
            $record['/head'] = "\033[0m";
        } elseif ($record['level'] >= Logger::NOTICE) {
            $record['head'] = '<comment>';
            $record['/head'] = '</comment>';
        } elseif ($record['level'] >= Logger::INFO) {
            $record['head'] = '<info>';
            $record['/head'] = '</info>';
        } else {
            $record['head'] = $record['body'] = $record['aux'] = "\033[1;30m";
            $record['/head'] = $record['/body'] = $record['/aux'] = "\033[0m";
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
            '',
            sprintf('%s: %s', get_class($e), $e->getMessage()),
            '',
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
        $result[] = '';

        $result = $this->createBlock($result,'fg=white;bg=red', '  ');

        array_unshift($result, '');
        array_unshift($result, '');
        array_push($result, '');

        return join(PHP_EOL, $result);
    }

    /**
     * Simplified realization from \Symfony\Component\Console\Style\SymfonyStyle
     *
     * @param $messages
     * @param null $style
     * @param string $prefix
     *
     * @return array
     */
    protected function createBlock($messages, $style = null, $prefix = ' ')
    {
        $indentLength = 0;
        $prefixLength = Helper::strlen($prefix);
        $lines = array();

        // wrap and add newlines for each element
        foreach ($messages as $key => $message) {
            $lines = array_merge($lines, explode(PHP_EOL, wordwrap($message, $this->lineLength - $prefixLength - $indentLength, PHP_EOL, true)));
        }

        foreach ($lines as $i => &$line) {
            $line = $prefix.$line;
            $line .= str_repeat(' ', $this->lineLength - Helper::strlen($line));
            if ($style) {
                $line = sprintf('<%s>%s</>', $style, $line);
            }
        }

        return $lines;
    }
}
