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

use Aes3xs\Yodler\Action\ActionInterface;
use Aes3xs\Yodler\Common\SharedMemoryHandler;
use Aes3xs\Yodler\Exception\RuntimeException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Report implementaion.
 */
class Report implements ReportInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var LockHandler
     */
    protected $lockHandler;

    /**
     * @var SharedMemoryHandler
     */
    protected $sharedMemoryHandler;

    /**
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param LockHandler $lockHandler
     * @param SharedMemoryHandler $sharedMemoryHandler
     */
    public function __construct(LockHandler $lockHandler, SharedMemoryHandler $sharedMemoryHandler)
    {
        $this->lockHandler = $lockHandler;
        $this->sharedMemoryHandler = $sharedMemoryHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->lockHandler->lock(true);
        $this->sharedMemoryHandler->dump([
            'deploys'         => [],
            'actions'         => [],
        ]);
        $this->lockHandler->release();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function reportDeploy(DeployContextInterface $deployContext)
    {
        $id = $this->getId();

        $this->lockHandler->lock(true);

        $deploy = [
            'deploy'     => $deployContext->getDeploy()->getName(),
            'scenario'   => $deployContext->getScenario()->getName(),
            'connection' => $deployContext->getConnection()->getName(),
            'actions'    => [],
            'failback'   => [],
        ];
        $actions = [];

        foreach ($deployContext->getScenario()->getActions()->all() as $action) {
            $key = spl_object_hash($action);
            $actions[$key] = [
                'name'   => $action->getName(),
                'state'  => self::ACTION_STATE_NONE,
            ];
            $deploy['actions'][$key] = [
                'name' => $action->getName(),
            ];
        }
        foreach ($deployContext->getScenario()->getFailbackActions()->all() as $action) {
            $key = spl_object_hash($action);
            $actions[$key] = [
                'name'   => $action->getName(),
                'state'  => self::ACTION_STATE_NONE,
            ];
            $deploy['failback'][$key] = [
                'name' => $action->getName(),
            ];
        }

        $data = $this->getData();
        $data['deploys'][$id] = $deploy;
        $data['actions'][$id] = $actions;
        $this->sharedMemoryHandler->dump($data);
        $this->lockHandler->release();
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionRunning(ActionInterface $action)
    {
        $this->setActionData($action, [
            'state' => self::ACTION_STATE_RUNNING,
            'start' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reportActionSucceed(ActionInterface $action, $output)
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
    public function reportActionError(ActionInterface $action, \Exception $e)
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
    public function reportActionSkipped(ActionInterface $action)
    {
        $this->setActionData($action, [
            'state'  => self::ACTION_STATE_SKIPPED,
            'finish' => date(self::DATE_FORMAT),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawData()
    {
        $this->lockHandler->lock(true);
        $data = $this->getData();
        $this->lockHandler->release();

        return $data;
    }

    /**
     * @return string
     */
    protected function getId()
    {
        if (!$this->id) {
            throw new RuntimeException('Report is not properly initialized');
        }

        return $this->id;
    }

    /**
     * @param ActionInterface $action
     * @param array $actionData
     */
    protected function setActionData(ActionInterface $action, array $actionData)
    {
        $key = spl_object_hash($action);
        $id = $this->getId();

        $this->lockHandler->lock(true);
        $data = $this->getData();
        if (!isset($data['actions'][$id])) {
            $data['actions'][$id] = [];
        }
        if (!isset($data['actions'][$id][$key])) {
            $data['actions'][$id][$key] = [];
        }
        $data['actions'][$id][$key] = $actionData + $data['actions'][$id][$key];
        $this->sharedMemoryHandler->dump($data);
        $this->lockHandler->release();
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('report');

        $rootNode
            ->children()
            ->arrayNode('actions')
                ->isRequired()
                ->prototype('array')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->defaultValue(null)->end()
                            ->scalarNode('state')->defaultValue(null)->end()
                            ->scalarNode('start')->defaultValue(null)->end()
                            ->scalarNode('finish')->defaultValue(null)->end()
                            ->scalarNode('output')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('deploys')
                ->prototype('array')
                ->children()
                    ->scalarNode('deploy')->isRequired()->end()
                    ->scalarNode('scenario')->isRequired()->end()
                    ->scalarNode('connection')->isRequired()->end()
                    ->arrayNode('actions')
                        ->isRequired()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->defaultValue(null)->end()
                                ->scalarNode('state')->defaultValue(null)->end()
                                ->scalarNode('start')->defaultValue(null)->end()
                                ->scalarNode('finish')->defaultValue(null)->end()
                                ->scalarNode('output')->defaultValue(null)->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('failback')
                        ->isRequired()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->defaultValue(null)->end()
                                ->scalarNode('state')->defaultValue(null)->end()
                                ->scalarNode('start')->defaultValue(null)->end()
                                ->scalarNode('finish')->defaultValue(null)->end()
                                ->scalarNode('output')->defaultValue(null)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $data = $this->sharedMemoryHandler->read();

        $processor = new Processor();
        $data = $processor->process($treeBuilder->buildTree(), [$data]);

        return $data;
    }
}
