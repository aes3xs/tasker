<?php

namespace Aes3xs\Yodler\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->getDefinition()->addOption(new InputOption('file', null, InputOption::VALUE_OPTIONAL, 'Config file to load', 'config.yml'));
    }
}
