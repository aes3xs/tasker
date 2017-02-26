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

use Aes3xs\Yodler\Deployer\DeployContextInterface;
use Aes3xs\Yodler\Variable\VariableFactoryInterface;
use Aes3xs\Yodler\Variable\VariableListInterface;
use Aes3xs\Yodler\Variable\VariableSuppliedInterface;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HeapFactory implements HeapFactoryInterface
{
    protected $container;
    protected $variables;
    protected $variableFactory;
    protected $input;
    protected $output;

    public function __construct(
        Container $container,
        VariableListInterface $variables,
        VariableFactoryInterface $variableFactory
    ) {
        $this->container = $container;
        $this->variables = $variables;
        $this->variableFactory = $variableFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DeployContextInterface $deployContext, InputInterface $input, OutputInterface $output)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        $heap = new Heap($twig, $expressionLanguage);

        $heap->addVariables($this->variables);
        $heap->addVariables($this->getContainerParameterVariables());
        $heap->addVariables($this->getContainerServiceVariables());

        $values = [
            'deployContext' => $deployContext,
            'connection'    => $deployContext->getConnection(),
            'input'         => $input,
            'output'        => $output,
        ];
        $heap->addVariables($this->variableFactory->createList($values));

        $connection = $deployContext->getConnection();
        if ($connection instanceof VariableSuppliedInterface) {
            $heap->addVariables($connection->getVariables());
        }

        $scenario = $deployContext->getScenario();
        if ($scenario instanceof VariableSuppliedInterface) {
            $heap->addVariables($scenario->getVariables());
        }

        $deploy = $deployContext->getDeploy();
        if ($deploy instanceof VariableSuppliedInterface) {
            $heap->addVariables($deploy->getVariables());
        }

        if ($input) {
            $heap->addVariables($this->getOptionVariables($input));
            $heap->addVariables($this->getArgumentVariables($input));
        }

        return $heap;
    }

    protected function getContainerParameterVariables()
    {
        $values = [];

        foreach ($this->container->getParameterBag()->all() as $name => $value) {
            $values[$name] = $value;
            $values[Inflector::camelize($name)] = $value;
        }

        return $this->variableFactory->createList($values);
    }

    protected function getContainerServiceVariables()
    {
        $values = [];

        foreach ($this->container->getServiceIds() as $name) {
            $values[$name] = $this->container->get($name);
            $values[Inflector::camelize($name)] = $this->container->get($name);
        }

        return $this->variableFactory->createList($values);
    }

    protected function getArgumentVariables(InputInterface $input)
    {
        $values = [];

        foreach ($input->getArguments() as $name => $value) {
            $values[$name] = $value;
            $values[Inflector::camelize($name)] = $value;
        }

        return $this->variableFactory->createList($values);
    }

    protected function getOptionVariables(InputInterface $input)
    {
        $values = [];

        foreach ($input->getOptions() as $name => $value) {
            $values[$name] = $value;
            $values[Inflector::camelize($name)] = $value;
        }

        return $this->variableFactory->createList($values);
    }
}
