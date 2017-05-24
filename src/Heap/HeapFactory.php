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

use Aes3xs\Yodler\Commander\CommanderFactory;
use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Scenario\Scenario;
use Aes3xs\Yodler\Service\Composer;
use Aes3xs\Yodler\Service\Git;
use Aes3xs\Yodler\Service\Releaser;
use Aes3xs\Yodler\Service\Shell;
use Aes3xs\Yodler\Service\Symfony;
use Aes3xs\Yodler\Variable\VariableList;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HeapFactory implements HeapFactoryInterface, EventSubscriberInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var CommanderFactory
     */
    protected $commanderFactory;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param CommanderFactory $commanderFactory
     */
    public function __construct(Container $container, CommanderFactory $commanderFactory)
    {
        $this->container = $container;
        $this->commanderFactory = $commanderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Scenario $scenario, Connection $connection, LoggerInterface $logger)
    {
        $variables = new VariableList();

        // Container parameters
        foreach ($this->container->getParameterBag()->all() as $name => $value) {
            $variables->set($name, $value);
        }

        // Container services
        foreach ($this->container->getServiceIds() as $name) {
            $variables->set($name, $this->container->get($name));
        }

        // Scenario variables
        if ($scenario->getVariables()) {
            foreach ($scenario->getVariables()->all() as $name => $value) {
                $variables->set($name, $value);
            }
        }

        // Connection variables
        if ($connection->getVariables()) {
            foreach ($connection->getVariables()->all() as $name => $value) {
                $variables->set($name, $value);
            }
        }

        // Input arguments
        foreach ($this->input->getArguments() as $name => $value) {
            $variables->set($name, $value);
        }

        // Input options
        foreach ($this->input->getOptions() as $name => $value) {
            $variables->set($name, $value);
        }

        $variables->set('scenario', $scenario);
        $variables->set('connection', $connection);
        $variables->set('input', $this->input);
        $variables->set('output', $this->output);
        $variables->set('logger', $logger);

        $commander = $this->commanderFactory->create($connection);
        $commander->setLogger($logger);
        $variables->set('commander', $commander);

        // Predefined helper services
        $shell = new Shell($commander);
        $variables->set('shell', $shell);
        $variables->set('releaser', new Releaser($shell));
        $variables->set('composer', new Composer($shell));
        $variables->set('git', new Git($shell));
        $variables->set('symfony', new Symfony($shell));

        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        $heap = new Heap($variables, $twig, $expressionLanguage);

        return $heap;
    }

    /**
     * @param ConsoleRunEvent $event
     */
    public function onConsoleRun(ConsoleRunEvent $event)
    {
        $this->input = $event->getInput();
        $this->output = $event->getOutput();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleRunEvent::NAME => ['onConsoleRun', 255],
        ];
    }
}
