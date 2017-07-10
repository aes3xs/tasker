<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Console;

use Aes3xs\Yodler\Common\CallableHelper;
use Aes3xs\Yodler\Deploy\DeployBuilder;
use Aes3xs\Yodler\Event\ConsoleRunEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds console commands from configured scenarios and deploys.
 */
class CommandBuilder implements EventSubscriberInterface
{
    /**
     * @var DeployBuilder
     */
    protected $deployBuilder;

    /**
     * @var array
     */
    protected $deploys;

    /**
     * Constructor.
     *
     * @param DeployBuilder $deployBuilder
     * @param array $deploys
     */
    public function __construct(DeployBuilder $deployBuilder, array $deploys)
    {
        $this->deployBuilder = $deployBuilder;
        $this->deploys = $deploys;
    }

    /**
     * @param ConsoleRunEvent $event
     */
    public function onConsoleRun(ConsoleRunEvent $event)
    {
        foreach ($this->deploys as $name => $data) {

            $deploy = $this->deployBuilder->build($name, $data);

            $command = new DeployCommand($deploy);

            if ($initializer = $deploy->getScenario()->getInitializer()) {
                CallableHelper::call($initializer, [
                    'command'     => $command,
                    'deploy'      => $deploy,
                    'application' => $event->getApplication(),
                    'input'       => $event->getInput(),
                    'output'      => $event->getOutput(),
                ] + $deploy->getParameters()->all());
            }

            $event->getApplication()->add($command);
        }
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
