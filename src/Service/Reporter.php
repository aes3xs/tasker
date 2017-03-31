<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Service;

use Aes3xs\Yodler\Deployer\ReportInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Reporter
 */
class Reporter
{
    const PICS = [
        ReportInterface::ACTION_STATE_NONE    => ' ',
        ReportInterface::ACTION_STATE_SKIPPED => '⇣',
        ReportInterface::ACTION_STATE_RUNNING => '➤',
        ReportInterface::ACTION_STATE_SUCCEED => '✔',
        ReportInterface::ACTION_STATE_ERROR   => '✘',
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
     * @var ReportInterface
     */
    protected $report;

    /**
     * Constructor.
     *
     * @param ReportInterface $report
     */
    public function __construct(ReportInterface $report)
    {
        $this->report = $report;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function printReport(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->report->getRawData();

        $i = 0;
        foreach ($result['deploys'] as $pid => $deploy) {
            $i++;
            $io->title('Deploy #' . $i);
            $io->text('<info>Scenario:</info> ' . $deploy['scenario']);
            $io->text('<info>Connection:</info> ' . $deploy['connection']);

            foreach ($deploy['actions'] as $key => $action) {
                $deploy['actions'][$key] = $result['actions'][$pid][$key] + $action;
            }
            foreach ($deploy['failback'] as $key => $action) {
                $deploy['failback'][$key] = $result['actions'][$pid][$key] + $action;
            }

            $total = 0;

            $rows = $actionsRows = self::buildRows($deploy['actions'], $actionsSucceed, $total);
            if ($actionsRows = self::buildRows($deploy['failback'], $failbackSucceed, $total)) {
                $rows[] = new TableSeparator();
                $rows = array_merge($rows, $actionsRows);
            }
            if ($actionsRows = self::buildRows($deploy['terminate'])) {
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
                $io->success('Deployed successfully');
            } elseif ($failbackSucceed) {
                $io->warning('Deploy failed, failback succeed');
            } else {
                $io->caution('Deploy failed, failback failed');
            }
        }
    }

    protected function buildRows(array $actionData, &$succeed = false, &$total = 0)
    {
        $pics = self::PICS;

        $succeed = true;
        $rows = [];
        foreach ($actionData as $key => $action) {
            $action = $action + self::ACTION_DEFAULTS;
            $start = new \DateTime($action['start']);
            $finish = new \DateTime($action['finish']);
            $action['output'] = preg_replace('/\s+/S', " ", $action['output']);
            $diff = $finish->getTimestamp() - $start->getTimestamp();
            $rows[] = [
                'name'     => $action['name'],
                'pic'      => isset($pics[$action['state']]) ? $pics[$action['state']] : $action['pic'],
                'state'    => $action['state'],
                'start'    => $start->format('H:i:s'),
                'duration' => $diff . 's',
                'output'   => mb_substr($action['output'], 0, 64),
            ];
            $succeed = $succeed && in_array($action['state'], [ReportInterface::ACTION_STATE_SKIPPED, ReportInterface::ACTION_STATE_SUCCEED]);
            $total += $diff;
        }
        return $rows;
    }
}
