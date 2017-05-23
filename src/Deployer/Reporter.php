<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Deployer;

use Aes3xs\Yodler\Common\LockableStorage;
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Scenario\Action;
use Aes3xs\Yodler\Scenario\Scenario;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Report implementaion.
 */
class Reporter implements ReporterInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var LockableStorage
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param LockableStorage $storage
     */
    public function __construct(LockableStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->storage->acquire(true);
        $this->storage->write([]);
        $this->storage->release();
    }

    /**
     * {@inheritdoc}
     */
    public function reportDeploy(Scenario $scenario, Connection $connection)
    {
        $this->storage->acquire(true);

        $deploy = [
            'scenario'   => $scenario->getName(),
            'connection' => $connection->getName(),
            'actions'    => [],
            'failbacks'  => [],
        ];

        foreach ($scenario->getActions() as $action) {
            $key = spl_object_hash($action);
            $deploy['actions'][$key] = ['name' => $action->getName()];
        }
        foreach ($scenario->getFailbacks() as $action) {
            $key = spl_object_hash($action);
            $deploy['failbacks'][$key] = ['name' => $action->getName()];
        }

        $data = $this->getData();
        $data[getmypid()] = $deploy;
        $this->storage->write($data);
        $this->storage->release();
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionRunning(Action $action)
    {
        $this->setActionData($action, [
            'state' => self::ACTION_STATE_RUNNING,
            'start' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionSucceed(Action $action, $output)
    {
        $this->setActionData($action, [
            'state'  => self::ACTION_STATE_SUCCEED,
            'output' => (string) $output,
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionError(Action $action, \Exception $e)
    {
        $this->setActionData($action, [
            'state'  => self::ACTION_STATE_ERROR,
            'output' => $e->getMessage(),
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionSkipped(Action $action)
    {
        $this->setActionData($action, [
            'state'  => self::ACTION_STATE_SKIPPED,
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    public function getRawData()
    {
        $this->storage->acquire(true);
        $data = $this->getData();
        $this->storage->release();

        return $data;
    }

    /**
     * @param Action $action
     * @param array $actionData
     */
    protected function setActionData(Action $action, array $actionData)
    {
        $this->storage->acquire(true);
        $data = $this->getData();

        $key = spl_object_hash($action);
        if (isset($data[getmypid()]) && isset($data[getmypid()]['actions'][$key])) {
            $data[getmypid()]['actions'][$key] = $actionData + $data[getmypid()]['actions'][$key];
        }
        if (isset($data[getmypid()]) && isset($data[getmypid()]['failbacks'][$key])) {
            $data[getmypid()]['failbacks'][$key] = $actionData + $data[getmypid()]['failbacks'][$key];
        }

        $this->storage->write($data);
        $this->storage->release();
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('reporter');

        $rootNode
            ->useAttributeAsKey('id')
            ->prototype('array')
            ->children()
                ->scalarNode('scenario')->isRequired()->end()
                ->scalarNode('connection')->isRequired()->end()
                ->arrayNode('actions')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->defaultValue(null)->end()
                            ->scalarNode('state')->defaultValue(self::ACTION_STATE_NONE)->end()
                            ->scalarNode('start')->defaultValue(null)->end()
                            ->scalarNode('finish')->defaultValue(null)->end()
                            ->scalarNode('output')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('failbacks')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->defaultValue(null)->end()
                            ->scalarNode('state')->defaultValue(self::ACTION_STATE_NONE)->end()
                            ->scalarNode('start')->defaultValue(null)->end()
                            ->scalarNode('finish')->defaultValue(null)->end()
                            ->scalarNode('output')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $data = $this->storage->read();

        $processor = new Processor();
        $data = $processor->process($treeBuilder->buildTree(), [$data]);

        return $data;
    }
}
