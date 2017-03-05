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
                $start = new \DateTime($action['start']);
                $finish = new \DateTime($action['finish']);
                $action['output'] = preg_replace('/\s+/S', " ", $action['output']);
                $rows[] = [
                    'name'     => $action['name'],
                    'pic'      => isset(self::$pics[$action['state']]) ? self::$pics[$action['state']] : $action['pic'],
                    'state'    => $action['state'],
                    'start'    => $start->format('H:i:s'),
                    'duration' => self::formatInterval($start, $finish),
                    'output'   => mb_substr($action['output'], 0, 64),
                ];
                $actionsSucceed = $actionsSucceed && in_array($action['state'], [ReportInterface::ACTION_STATE_SKIPPED, ReportInterface::ACTION_STATE_SUCCEED]);
            }
            $rows[] = new TableSeparator();
            foreach ($deploy['failback'] as $key => $action) {
                $action = $result['actions'][$pid][$key] + $action + self::$actionDefaults;
                $start = new \DateTime($action['start']);
                $finish = new \DateTime($action['finish']);
                $action['output'] = preg_replace('/\s+/S', " ", $action['output']);
                $rows[] = [
                    'name'     => $action['name'],
                    'pic'      => isset(self::$pics[$action['state']]) ? self::$pics[$action['state']] : $action['pic'],
                    'state'    => $action['state'],
                    'start'    => $start->format('H:i:s'),
                    'duration' => self::formatInterval($start, $finish),
                    'output'   => mb_substr($action['output'], 0, 64),
                ];
                $failbackSucceed = $failbackSucceed && in_array($action['state'], [ReportInterface::ACTION_STATE_SKIPPED, ReportInterface::ACTION_STATE_SUCCEED]);
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'ℹ', 'State', 'Start', 'Duration', 'Output'])
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

    protected static function formatInterval(\DateTime $start, \DateTime $finish)
    {
        $diff = $finish->diff($start);

        $intervals = [
            $diff->y ?  $diff->y . ' years' : null,
            $diff->m ?  $diff->m . ' months' : null,
            $diff->d ?  $diff->d . ' days' : null,
            $diff->h ?  $diff->h . 'h' : null,
            $diff->m ?  $diff->m . 'm' : null,
            $diff->s ?  $diff->s . 's' : null,
        ];

        $intervals = array_filter($intervals);

        return $intervals ? implode(' ', $intervals) : '0s';
    }
}
