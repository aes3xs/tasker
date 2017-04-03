<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Heap;

use Aes3xs\Yodler\Connection\ConnectionInterface;
use Aes3xs\Yodler\Scenario\ScenarioInterface;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HeapFactory implements HeapFactoryInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var VariableFactoryInterface
     */
    protected $variableFactory;

    /**
     * @var VariableListInterface
     */
    protected $runtime;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param VariableFactoryInterface $variableFactory
     * @param VariableListInterface $runtime
     */
    public function __construct(
        Container $container,
        VariableFactoryInterface $variableFactory,
        VariableListInterface $runtime
    ) {
        $this->container = $container;
        $this->variableFactory = $variableFactory;
        $this->runtime = $runtime;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        ScenarioInterface $scenario,
        ConnectionInterface $connection,
        InputInterface $input,
        OutputInterface $output
    ) {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        $heap = new Heap($twig, $expressionLanguage);

        $heap->addVariables($this->getContainerParameterVariables());
        $heap->addVariables($this->getContainerServiceVariables());

        $values = [
            'scenario'   => $scenario,
            'connection' => $connection,
            'input'      => $input,
            'output'     => $output,
        ];
        $heap->addVariables($this->variableFactory->createList($values));

        $heap->addVariables($scenario->getVariables());
        $heap->addVariables($connection->getVariables());

        $heap->addVariables($this->getArgumentVariables($input));
        $heap->addVariables($this->getOptionVariables($input));

        $heap->addVariables($this->runtime);

        return $heap;
    }

    protected function getContainerParameterVariables()
    {
        return $this->variableFactory->createList($this->container->getParameterBag()->all());
    }

    protected function getContainerServiceVariables()
    {
        $values = [];
        foreach ($this->container->getServiceIds() as $name) {
            $values[$name] = $this->container->get($name);
        }
        return $this->variableFactory->createList($values);
    }

    protected function getArgumentVariables(InputInterface $input)
    {
        $list = array_filter($input->getArguments(), function($value) {
            return !is_null($value);
        });
        return $this->variableFactory->createList($list);
    }

    protected function getOptionVariables(InputInterface $input)
    {
        $list = array_filter($input->getOptions(), function($value) {
            return !is_null($value);
        });
        return $this->variableFactory->createList($list);
    }
}
