<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Functional;

use Aes3xs\Yodler\Common\ReportPrinter;
use Aes3xs\Yodler\Tests\AbstractFunctionalTest;

class DeployTest extends AbstractFunctionalTest
{
    public function testDeploy()
    {
        $semaphore = $this->getContainer()->get('semaphore_factory')->create('_test');
        $reporter = $this->getContainer()->get('reporter_factory')->create('_test');

        $semaphore->reset();
        $reporter->reset();
        $semaphore->addProcess(getmypid());
        $semaphore->run();

        $deploy = $this->getContainer()->get('deploy_builder')->build('test', $this->getContainer()->getParameter('deploys')['test']);

        $this->getContainer()->get('deployer')->deploy($deploy, $semaphore, $reporter);

        ReportPrinter::printReport($reporter, $this->input, $this->output);
    }
}
