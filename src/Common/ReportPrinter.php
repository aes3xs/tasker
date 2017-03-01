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

use Aes3xs\Yodler\Deployer\ReportInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Report printer is used to print deploy results to console.
 */
class ReportPrinter
{
    /**
     * @var array
     */
    protected static $pics = [
        ReportInterface::ACTION_STATE_NONE    => ' ',
        ReportInterface::ACTION_STATE_SKIPPED => '⇣',
        ReportInterface::ACTION_STATE_RUNNING => '➤',
        ReportInterface::ACTION_STATE_SUCCEED => '✔',
        ReportInterface::ACTION_STATE_ERROR   => '✘',
    ];

    /**
     * @var array
     */
    protected static $actionDefaults = [
        'name'   => null,
        'pic'    => '❓',
        'state'  => null,
        'start'  => null,
        'finish' => null,
        'output' => null,
    ];

    /**
     * @param $result
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public static function printResult($result, InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $i = 0;
        foreach ($result['deploys'] as $pid => $deploy) {
            $i++;
            $io->title('Deploy #' . $i);
            $io->text('<info>Scenario:</info> ' . $deploy['scenario']);
            $io->text('<info>Connection:</info> ' . $deploy['connection']);

            $rows = [];
            $actionsSucceed = true;
            $failbackSucceed = true;
            foreach ($deploy['actions'] as $key => $action) {
                $action = $result['actions'][$pid][$key] + $action + self::$actionDefaults;
                $rows[] = [
                    'name'   => $action['name'],
                    'pic'    => isset(self::$pics[$action['state']]) ? self::$pics[$action['state']] : $action['pic'],
                    'state'  => $action['state'],
                    'start'  => $action['start'],
                    'finish' => $action['finish'],
                    'output' => mb_substr($action['output'], 0, 32),
                ];
                $actionsSucceed = $actionsSucceed && in_array($action['state'], [ReportInterface::ACTION_STATE_SKIPPED, ReportInterface::ACTION_STATE_SUCCEED]);
            }
            $rows[] = new TableSeparator();
            foreach ($deploy['failback'] as $key => $action) {
                $action = $result['actions'][$pid][$key] + $action + self::$actionDefaults;
                $rows[] = [
                    'name'   => $action['name'],
                    'pic'    => isset(self::$pics[$action['state']]) ? self::$pics[$action['state']] : $action['pic'],
                    'state'  => $action['state'],
                    'start'  => $action['start'],
                    'finish' => $action['finish'],
                    'output' => mb_substr($action['output'], 0, 32),
                ];
                $failbackSucceed = $failbackSucceed && in_array($action['state'], [ReportInterface::ACTION_STATE_SKIPPED, ReportInterface::ACTION_STATE_SUCCEED]);
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'ℹ', 'State', 'Start', 'Finish', 'Output'])
                ->setRows($rows);
            $table->setStyle('borderless');
            $table->render();

            if ($actionsSucceed) {
                $io->success('Deployed successfully');
            } elseif ($failbackSucceed) {
                $io->warning('Deploy failed, failback succeed');
            } else {
                $io->caution('Deploy failed, failback failed');
            }
        }
    }
}
