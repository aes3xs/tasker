<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Report;

use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Reporter is used to collect information about actions and print result.
 */
class Reporter
{
    /**
     * @var resource
     */
    protected $inSocket;

    /**
     * @var resource
     */
    protected $outSocket;

    /**
     * @var SymfonyStyle
     */
    protected $style;

    const DATE_FORMAT = 'Y-m-d H:i:s';

    const ACTION_STATE_NONE = 'None';
    const ACTION_STATE_SKIPPED = 'Skipped';
    const ACTION_STATE_RUNNING = 'Running';
    const ACTION_STATE_SUCCEED = 'Succeed';
    const ACTION_STATE_ERROR = 'Error';

    const PICS = [
        self::ACTION_STATE_NONE    => ' ',
        self::ACTION_STATE_SKIPPED => '⇣',
        self::ACTION_STATE_RUNNING => '➤',
        self::ACTION_STATE_SUCCEED => '✔',
        self::ACTION_STATE_ERROR   => '✘',
    ];

    const ACTION_DEFAULTS = [
        'name'    => null,
        'pic'     => '❓',
        'state'   => null,
        'start'   => null,
        'finish'  => null,
        'output'  => null,
        'isGroup' => false,
    ];

    /**
     * Constructor.
     *
     * @param SymfonyStyle $style
     */
    public function __construct(SymfonyStyle $style)
    {
        $this->style = $style;

        $sockets = [];
        $domain = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX);
        if (socket_create_pair($domain, SOCK_STREAM, 0, $sockets) === false) {
            throw new RuntimeException("Socket pair creation failed: " . socket_strerror(socket_last_error()));
        }

        $this->inSocket = $sockets[0];
        $this->outSocket = $sockets[1];
    }

    /**
     * Report about action.
     *
     * @param string $actionName
     */
    public function reportAction($actionName)
    {
        $this->sendActionData([
            'name'  => $actionName,
            'state' => self::ACTION_STATE_NONE,
        ]);
    }

    /**
     * Report about action group.
     *
     * @param string $actionGroupName
     */
    public function reportActionGroup($actionGroupName)
    {
        $this->sendActionData([
            'name'    => $actionGroupName,
            'isGroup' => true,
        ]);
    }

    /**
     * Report about running action.
     *
     * @param string $actionName
     */
    public function reportActionRunning($actionName)
    {
        $this->sendActionData([
            'name'  => $actionName,
            'state' => self::ACTION_STATE_RUNNING,
            'start' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * Report about succeed action.
     *
     * @param string $actionName
     * @param $output
     */
    public function reportActionSucceed($actionName, $output)
    {
        $this->sendActionData([
            'name'   => $actionName,
            'state'  => self::ACTION_STATE_SUCCEED,
            'output' => (string)$output,
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * Report about error occured while running action.
     *
     * @param string $actionName
     * @param \Exception $e
     */
    public function reportActionError($actionName, \Exception $e)
    {
        $this->sendActionData([
            'name'   => $actionName,
            'state'  => self::ACTION_STATE_ERROR,
            'output' => $e->getMessage(),
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * Report about skipped action.
     *
     * @param string $actionName
     */
    public function reportActionSkipped($actionName)
    {
        $this->sendActionData([
            'name'   => $actionName,
            'state'  => self::ACTION_STATE_SKIPPED,
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * @param array $actionData
     */
    protected function sendActionData(array $actionData)
    {
        $data = json_encode($actionData) . PHP_EOL;
        if (false === socket_write($this->inSocket, $data, strlen($data))) {
            throw new RuntimeException("Socket write failed: " . socket_strerror(socket_last_error($this->inSocket)));
        }
    }

    /**
     * Print report.
     */
    public function printReport()
    {
        $this->style->newLine();

        $data = "";
        while ($res = socket_recv($this->outSocket, $buf, 2048, MSG_DONTWAIT)) {
            $data .= $buf;
        }

        $records = $data ? array_filter(explode(PHP_EOL, $data)) : [];

        if (!$records) {
            $this->style->text("<info>No report data</info>");
            return;
        }

        $actions = [];
        if ($data) {
            foreach (array_filter(explode(PHP_EOL, $data)) as $record) {
                $recordData = json_decode($record, true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    $this->style->error("Error deserializing record: " . $record);
                    continue;
                }

                $key = null === $recordData['name'] ? uniqid() : $recordData['name'];

                $actions[$key] = $recordData + (isset($actions[$key]) ? $actions[$key] : self::ACTION_DEFAULTS);
            }
        }

        $total = 0;

        $succeed = true;
        $rows = [];
        foreach ($actions as $action) {
            $action = $action + self::ACTION_DEFAULTS;

            if ($action['isGroup']) {
                if (!empty($rows)) {
                    $rows[] = new TableSeparator();
                    $rows[] = [new TableCell("<comment>Action group: " . $action['name'] . "</comment>", ['colspan' => 6])];
                }
                continue;
            }

            $diff = null;
            $start = null;
            if ($action['start'] && $action['finish']) {
                $actionStart = new \DateTime($action['start']);
                $actionFinish = new \DateTime($action['finish']);
                $start = $actionStart->format('H:i:s');
                $diff = $actionFinish->getTimestamp() - $actionStart->getTimestamp();
                $diff = $diff . 's';
            }

            $action['output'] = preg_replace('/\s+/S', " ", $action['output']);

            $rows[] = [
                'name'     => $action['name'],
                'pic'      => array_key_exists($action['state'], self::PICS) ? self::PICS[$action['state']] : $action['pic'],
                'state'    => $action['state'],
                'start'    => $start,
                'duration' => $diff,
                'output'   => mb_substr($action['output'], 0, 64),
            ];
            $succeed = $succeed && in_array($action['state'], [Reporter::ACTION_STATE_SKIPPED, Reporter::ACTION_STATE_SUCCEED]);

            $total += $diff;
        }

        $table = new Table($this->style);
        $table
            ->setHeaders(['Name', 'ℹ', 'State', 'Start', 'Duration', 'Output'])
            ->setRows($rows);
        $table->setStyle('borderless');
        $table->render();

        $this->style->text("<info>Total:</info> {$total}s");

        if ($succeed) {
            $this->style->success("Completed successfully");
        } else {
            $this->style->error("Failed");
        }
    }
}
