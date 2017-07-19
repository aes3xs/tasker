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
use Aes3xs\Yodler\Deploy\Deploy;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Aes3xs\Yodler\Service\Composer;
use Aes3xs\Yodler\Service\Git;
use Aes3xs\Yodler\Service\Releaser;
use Aes3xs\Yodler\Service\Shell;
use Aes3xs\Yodler\Service\Symfony;
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
    public function create(Deploy $deploy, LoggerInterface $logger)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $expressionLanguage = new ExpressionLanguage();

        $heap = new Heap($twig, $expressionLanguage);

        // Container parameters
        foreach ($this->container->getParameterBag()->all() as $name => $value) {
            $heap->set($name, $value);
        }

        // Container services
        foreach ($this->container->getServiceIds() as $name) {
            $heap->set($name, $this->container->get($name));
        }

        // Deploy parameters
        foreach ($deploy->getParameters()->all() as $name => $value) {
            $heap->set($name, $value);
        }

        // Input arguments
        foreach ($this->input->getArguments() as $name => $value) {
            $heap->set($name, $value);
        }

        // Input options
        foreach ($this->input->getOptions() as $name => $value) {
            $heap->set($name, $value);
        }

        $heap->set('deploy', $deploy);
        $heap->set('connection', $deploy->getConnection());
        $heap->set('logger', $logger);

        $heap->set('input', $this->input);
        $heap->set('output', $this->output);

        $commander = $this->commanderFactory->createLazy($deploy->getConnection(), $heap, $logger);
        $heap->set('commander', $commander);

        // Predefined helper services
        $shell = new Shell($commander);
        $heap->set('shell', $shell);
        $heap->set('releaser', new Releaser($shell));
        $heap->set('composer', new Composer($shell));
        $heap->set('git', new Git($shell));
        $heap->set('symfony', new Symfony($shell));

        $heap->set('heap', $heap);

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
            ConsoleRunEvent::NAME  => ['onConsoleRun', 255],
        ];
    }
}
