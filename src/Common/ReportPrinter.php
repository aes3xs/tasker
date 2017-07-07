<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Common;

use Aes3xs\Yodler\Deployer\Reporter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Reporter
 */
class ReportPrinter
{
    const PICS = [
        Reporter::ACTION_STATE_NONE    => ' ',
        Reporter::ACTION_STATE_SKIPPED => '⇣',
        Reporter::ACTION_STATE_RUNNING => '➤',
        Reporter::ACTION_STATE_SUCCEED => '✔',
        Reporter::ACTION_STATE_ERROR   => '✘',
    ];

    const ACTION_DEFAULTS = [
        'name'   => null,
        'pic'    => '❓',
        'state'  => null,
        'start'  => null,
        'finish' => null,
        'output' => null,
    ];

    /**
     * Print report.
     *
     * @param Reporter $reporter
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public static function printReport(Reporter $reporter, InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $result = $reporter->getRawData();

        foreach ($result as $pid => $deploy) {
            $io->text("<info>Deploy:</info> {$deploy['name']}");

            $total = 0;

            $rows = $actionsRows = self::buildRows($deploy['actions'], $actionsSucceed, $total);
            if ($actionsRows = self::buildRows($deploy['failbacks'], $failbackSucceed, $total)) {
                $rows[] = new TableSeparator();
                $rows = array_merge($rows, $actionsRows);
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'ℹ', 'State', 'Start', 'Duration', 'Output'])
                ->setRows($rows);
            $table->setStyle('borderless');
            $table->render();

            $io->text("<info>Total:</info> {$total}s");

            if ($actionsSucceed) {
                $io->success('Success');
            } elseif ($failbackSucceed) {
                $io->warning('Failed, failback succeed');
            } else {
                $io->caution('Failed, failback failed');
            }
        }
    }

    protected static function buildRows(array $actionData, &$succeed, &$total)
    {
        $pics = self::PICS;

        $succeed = true;
        $rows = [];
        foreach ($actionData as $key => $action) {
            $action = $action + self::ACTION_DEFAULTS;

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
                'pic'      => isset($pics[$action['state']]) ? $pics[$action['state']] : $action['pic'],
                'state'    => $action['state'],
                'start'    => $start,
                'duration' => $diff,
                'output'   => mb_substr($action['output'], 0, 64),
            ];
            $succeed = $succeed && in_array($action['state'], [Reporter::ACTION_STATE_SKIPPED, Reporter::ACTION_STATE_SUCCEED]);
            $total += $diff;
        }
        return $rows;
    }
}
