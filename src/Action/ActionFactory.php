<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Action;

use Aes3xs\Yodler\Deployer\SemaphoreInterface;
use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Action factory implementation.
 */
class ActionFactory implements ActionFactoryInterface
{
    /**
     * @var VariableListInterface
     */
    protected $variables;

    /**
     * @var SemaphoreInterface
     */
    protected $semaphore;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param VariableListInterface $variables
     * @param SemaphoreInterface $semaphore
     * @param LoggerInterface $logger
     */
    public function __construct(
        VariableListInterface $variables,
        SemaphoreInterface $semaphore,
        LoggerInterface $logger
    ) {
        $this->variables = $variables;
        $this->semaphore = $semaphore;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        switch (true) {

            case array_key_exists('task', $data):
                $action = $this->createTaskAction($data['task'], $data);
                break;

            case array_key_exists('message', $data):
                $action = $this->createMessageAction($data['message'], $data);
                break;

            case array_key_exists('checkpoint', $data):
                $action = $this->createCheckpointAction($data['checkpoint']);
                break;

            default:
                throw new RuntimeException('Action type cannot be recognized: ' . var_export($data, true));
        }

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function createList()
    {
        return new ActionList();
    }

    /**
     * {@inheritdoc}
     */
    public function createListFromConfiguration($actionsConfiguration)
    {
        $processor = new Processor();
        $actionsConfiguration = $processor->process($this->getConfigTreeBuilder()->buildTree(), [$actionsConfiguration]);

        $actions = new ActionList();

        foreach ($actionsConfiguration as $actionData) {
            $action = $this->create($actionData);
            $actions->add($action);
        }

        return $actions;
    }

    /**
     * @param $taskName
     * @param array $data
     *
     * @return TaskAction
     */
    protected function createTaskAction($taskName, array $data)
    {
        $condition = isset($data['condition']) ? $data['condition'] : null;
        return new TaskAction($taskName, $condition);
    }

    /**
     * @param $message
     * @param array $data
     *
     * @return MessageAction
     */
    protected function createMessageAction($message, array $data)
    {
        $level = isset($data['level']) ? $data['level'] : 'notice';
        return new MessageAction($message, $level, $this->logger);
    }

    /**
     * @param $checkpoint
     *
     * @return CheckpointAction
     */
    protected function createCheckpointAction($checkpoint)
    {
        return new CheckpointAction($checkpoint, $this->semaphore);
    }

    /**
     * @return TreeBuilder
     */
    protected function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('actions');

        $rootNode
            ->prototype('variable')
                ->validate()
                ->ifTrue(function ($node) {
                    return !is_array($node) || !$node;
                })
                ->thenInvalid('Action definition should be non-empty array, got: %s')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
