<?php

namespace Aes3xs\Yodler\Logger;

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Heap\HeapInterface;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter as BaseConsoleFormatter;

class ConsoleFormatter extends BaseConsoleFormatter
{
    const SIMPLE_DATE = 'H:i:s';
    const SIMPLE_FORMAT = "%start_tag%[%datetime%] %level_name% %channel%:%end_tag% %message% %context% %extra%\n";

    /**
     * @var HeapInterface
     */
    protected $heap;

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

        return parent::format($record);
    }

    protected function convertToString($data)
    {
        if (is_scalar($data)) {
            return parent::convertToString($data);
        }

        return var_export($this->normalize($data), true);
    }

    public function getChannelName(DeployContextInterface $deployContext)
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
