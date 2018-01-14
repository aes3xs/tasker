<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler;

use Aes3xs\Yodler\Exception\RuntimeException;
use Aes3xs\Yodler\Exception\SkipActionException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class AbstractRecipe
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    protected $state = self::STATE_NONE;

    /**
     * @var \Exception|null
     */
    protected $exception;

    const STATE_NONE = 'none';
    const STATE_RUNNING = 'running';
    const STATE_FAILED = 'failed';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->configure();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
    }

    /**
     * Adds an argument.
     *
     * @param string $name        The argument name
     * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param string $description A description text
     * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
     *
     * @return $this
     */
    protected function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->getContainer()->get('application.command')->addArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * Adds an option.
     *
     * @param string $name        The option name
     * @param string $shortcut    The shortcut (can be null)
     * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
     * @param string $description A description text
     * @param mixed  $default     The default value (must be null for InputOption::VALUE_NONE)
     *
     * @return $this
     */
    protected function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->getContainer()->get('application.command')->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    protected static function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $loader = new YamlFileLoader($containerBuilder, new FileLocator([__DIR__]));
        $loader->load('Resources/config/config.yml');

        $containerBuilder->compile();

        return $containerBuilder;
    }

    public static function run($action = 'execute')
    {
        $container = static::buildContainer();
        $recipe = new static($container);

        $container->set('recipe', $recipe);

        $input = $container->get('input');
        $output = $container->get('output');
        $application = $container->get('application');
        $application->run($input, $output);

        try {
            // Constructor contains soket initialization, call it before fork
            $reporter = $container->get('reporter');

            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new RuntimeException('Could not fork');
            } else if ($pid) {
                // Parent process code
                pcntl_waitpid($pid, $status);

                $reporter->printReport();
            } else {
                // Child process code
                // Running actual work in isolated process

                $container->get('runner')->runAction($action);
                return;
            }
        } catch(\Exception $e) {
            $application->renderException($e, $output);
        }
    }

    protected function runActions(array $actions, $actionGroupName = null)
    {
        $this->getContainer()->get('runner')->runActions($actions, $actionGroupName);
    }

    protected function runAction($actionName)
    {
        $this->getContainer()->get('runner')->runAction($actionName);
    }

    protected function getAvailableActions()
    {
        return $this->getContainer()->get('runner')->getAvailableActions();
    }

    protected function skipAction()
    {
        throw new SkipActionException();
    }

    protected function get($name)
    {
        return $this->getContainer()->get('resource_resolver')->resolveResource($name);
    }

    protected function askConfirmationQuestion($question, $default = true)
    {
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion("<info>$question</info> <comment>" . ($default ? '(Y/n)' : '(y/N)') . "</comment> ", $default);
        return $helper->ask($this->getContainer()->get('input'), $this->getContainer()->get('output'), $question);
    }

    protected function askChoiceQuestion($question, array $variants, $default = null)
    {
        $helper = new QuestionHelper();
        $question = new ChoiceQuestion("<info>$question</info>", $variants, $default);
        return $helper->ask($this->getContainer()->get('input'), $this->getContainer()->get('output'), $question);
    }

    protected function askQuestion($question, $default = null)
    {
        $helper = new QuestionHelper();
        $question = new Question("<info>$question</info>", $default);
        return $helper->ask($this->getContainer()->get('input'), $this->getContainer()->get('output'), $question);
    }
}
