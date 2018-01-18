<?php

/*
 * This file is part of the Tasker package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Tasker\Tests;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AbstractRecipeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ContainerBuilder
     */
    protected function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $loader = new YamlFileLoader($containerBuilder, new FileLocator([dirname(__DIR__)]));
        $loader->load('src/Resources/config/config.yml');

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $containerBuilder->set('input', $input);
        $containerBuilder->set('output', $output);

        $containerBuilder->compile();

        return $containerBuilder;
    }

    public function testRunAction()
    {
        require_once __DIR__ . '/Fixtures/recipe/TestRecipe.php';

        $container = $this->buildContainer();

        $recipe = new \TestRecipe($container);

        $container->set('recipe', $recipe);

        $container->get('runner')->runAction('execute');
    }
}
