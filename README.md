Tasker
======
Tasker is an automation tool to write small and simple deploy scripts.  
It's suitable for one-server projects without CI integrations.  
If you want more, please take a look at Jenkins, TeamCity and other tools. It's not pretent to replace them. 
Tasker inspired by Deployer project, you can also take a look for it. Tasker is designed to be more flexible (overridable) and more object-oriented, but less featured.  

Installation
------------
Install from composer  
```composer require aes3xs/tasker```

How to deploy
-------------
A good way to go will be splitting deployment into local and remote parts. 
   
Local part prepares code, warms up file caches, downloads vendors and other operations, which affects everything *inside* project dir.  
No external dependencies, services or files (except vendor managers and php itself).  
No database requests, no migrations.  
This script can be executed everythere, on build server or local machines. It creates ready-to-deploy module which can be copied and run on production as is.  
It's pretty useful to have such script in project and it should't have dependencies, such as composer. Because it's responsible to run it, if you have bare broject from repo.  
Some of these steps sometimes defined in composer.json scripts section. But I prefer to have standalone file.  

Here is an example (./bin/deploy):
 ```bash
#!/bin/bash  
  
set -e
set -o pipefail
  
SYMFONY_ENV=${SYMFONY_ENV:="dev"}
SYMFONY_DEBUG=${SYMFONY_DEBUG:="1"}
for i in "$@"; do case $i in -e=*|--env=*) SYMFONY_ENV="${i#*=}"; shift;; --no-debug) SYMFONY_DEBUG="0"; shift;; *);; esac done
  
ROOT="$(dirname "$(dirname "$(readlink -fm "$0")")")"
PHP=$(which "php") || { echo "PHP is not found" ; exit 1; }
YARN=$(which "yarn") || { echo "Yarn is not found" ; exit 1; }
COMPOSER=$(which "composer") || { echo "Composer is not found" ; exit 1; }
if [ "$SYMFONY_DEBUG" == "0" ]; then NO_DEBUG="--no-debug"; fi
CONSOLE="${ROOT}/bin/console --quiet --env=${SYMFONY_ENV} ${NO_DEBUG}"
  
echo "SYMFONY_ENV   = ${SYMFONY_ENV}"
echo "SYMFONY_DEBUG = ${SYMFONY_DEBUG}"
echo "PROJECT_ROOT  = ${ROOT}"
echo "PHP           = ${PHP}"
echo "YARN          = ${YARN}"
echo "COMPOSER      = ${COMPOSER}"
echo "CONSOLE       = ${CONSOLE}"
echo ""
  
_exec () {
    echo -e "\033[1m[$(date +%T)] >\033[0m" $1
    eval $1
}
  
# Vendors
_exec "cd ${ROOT} && ${COMPOSER} install --prefer-dist --no-progress --no-interaction --optimize-autoloader"
_exec "cd ${ROOT} && ${YARN} install --prod --non-interactive"
  
# Cache
_exec "rm -rf ${ROOT}/var/cache/${SYMFONY_ENV}"
_exec "chmod 0775 ${ROOT}/var/cache"
_exec "${CONSOLE} cache:warmup"
  
# Assets
PATHS=(
"${ROOT}/web/js"
"${ROOT}/web/css"
"${ROOT}/web/bundles"
)
_exec "rm -rf ${PATHS[*]}"
_exec "${CONSOLE} assets:install ${ROOT}/web --symlink --relative"
_exec "${CONSOLE} assetic:dump ${ROOT}/web"
  
# Writable
_exec "if [ ! -w ${ROOT}/var/cache ]; then { echo 'Is not writable' ; exit 1; }; fi"
_exec "if [ ! -w ${ROOT}/var/logs ]; then { echo 'Is not writable' ; exit 1; }; fi"
_exec "if [ ! -w ${ROOT}/var/spool ]; then { echo 'Is not writable' ; exit 1; }; fi"

```

You can also write this with Tasker, but I'd suggest to keep separate shell script.  

Remote part of deployment process works with external services, such as nginx, php-fpm, databases.  
Also you must have access to project repository to clone it.  

There are few important things.  

First, executing commands from another user.  
For security reasons, each person on server must have it's own credentials.  
But project itself configured to work from one user, for example, `www-data`.  
So you must add something like `sudo -u USER` to each call, and it's already implemented.  

Second, authentication to clone repo on server.  
GitLab has login/pass option or public key, for example.  
So better way to use key to SSH, and same key to authenticate to GitLab, or another system.  
This can be done with SSH forwarding authentication.  

Deploy script example

```php
#!/usr/bin/env php
<?php
  
require_once "./vendor/autoload.php";  
  
use Aes3xs\Tasker\Connection\Connection;
use Aes3xs\Tasker\Service\Git;
use Aes3xs\Tasker\Service\Releaser;
use Aes3xs\Tasker\Service\Shell;
use Aes3xs\Tasker\Service\Symfony;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    public $repository = 'REPOSITORY_PATH';
    public $deployPath = '/var/www/PROJECT';
    public $runUser = 'www-data';
    public $branch = 'master';

    protected function configure()
    {
        $this
            ->addArgument('server', InputArgument::REQUIRED)
            ->addOption('branch', 'b', InputOption::VALUE_OPTIONAL);
    }

    public function execute($server, Connection $connection)
    {
        if (!in_array($server, ['prod', 'dev'])) {
            throw new \RuntimeException('Server available values: prod, dev');
        }

        $connection
            ->getParameters()
            ->setHost(null)
            ->setForwarding(true); // Configure to deploy on different servers

        $this->branch = 'master'; // Set branch if needed

        try {
            $this->runActions(array_filter($this->getAvailableActions(), function ($actionName) {
                return !in_array($actionName, ['execute', 'failNotify']);
            }), 'Deploy');
        } catch (\Exception $e) {
            $this->runActions(['failNotify'], 'Failback');
        }
    }
    
    public function switchUser(Shell $shell, $runUser)
    {
        $shell->setUser($runUser);

        return $runUser;
    }
    
    public function createRelease(Releaser $releaser, Git $git, Shell $shell, $deployPath, $repository, $branch)
    {
        $releaser->setDeployPath($deployPath);
        $releaser->prepare();

        if ($releaser->isLocked() && $this->askConfirmationQuestion('Deploy is locked. Unlock?')) {
            $releaser->unlock();
        }
        $releaser->lock();

        $releaser->create();
        $releasePath = $releaser->getReleasePath();

        $releases = $releaser->getReleaseList();
        $reference = $releases ? $releaser->getReleasePathByName(reset($releases)) : null;

        $shell->setCwd($releasePath);

        $git->cloneAt($repository, $releasePath, $branch, $reference); // Uses SSH forwarding if presented

        $releaser->updateReleaseShares(['var/logs', 'var/spool', 'var/sessions'], ['app/config/parameters.yml']);

        $shell->chmod('./bin', 0777);
        $shell->exec('./bin/deploy --env=prod --no-debug');
    }
    
    public function migrate(Symfony $symfony)
    {
        $symfony->runCommand('doctrine:migrations:migrate', [], ['allow-no-migration']);
    }

    public function release(Releaser $releaser, Git $git, Shell $shell, $server, $branch)
    {
        $releaser->release();
        $releaser->unlock();

        $last_commits = $git->log($releaser->getReleasePath(), 3);

        $this->info(<<<EOL
Server: {{ server }}
Released: {{ releaser.getReleaseName() }}
Branch: {{ server }}
Last commits:
$last_commits
EOL
        );

        $releaser->cleanup(5);

        $shell->setUser(null);

        $shell->exec("sudo service nginx reload");
        $shell->exec("sudo service php-fpm reload");
    }

    public function shutdownRoutine(Shell $shell)
    {
        $shell->setUser(null);
    }

    public function failNotify()
    {
        $this->error(<<<EOL
Server: {{ server }}
Release: {{ releaser.getReleaseName() }}
Branch: {{ server }}
FALURE
EOL
        );
    }
}

\TestRecipe::run();

```

Overview
--------

Tasker is intended to create php binaries with a set of actions.  
Actions can be executed on local or remote server.  
They are located in "recipe" class.  
Recipe is a self-executable console command.  

```php
#!/usr/bin/env php
<?php  
  
require_once "./vendor/autoload.php";
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    public function execute()
    {
        // Do smth
    }
}
  
\TestRecipe::run();

```
Make your file executable with first line`#!/usr/bin/env php`   
Add permissions `chmod a+x ./testRecipe`   
Then add autoload from composer `require_once "./vendor/autoload.php";`, make sure path is correct.  
Define your own recipe class, extend it from `\Aes3xs\Tasker\AbstractRecipe`  
Add `execute` (default) or other method, which will be executed first.  
Call static `::run()` or `::run('yourMethodName')`  
  
Actions
-------
Inside actions you can actually do your work.  
Actions are public non-static methods.  
Magic methods (__*) or started with get[A-Z] (getSmth, for example) cannot be executed.  
Actions are called with `runActions(['prepare', 'release'])` or `runAction('release'')`  
You cat get list of all available actions in recipe with `getAvailableActions()`  
If you want to skip action during it execution, call `skipAction('only in production')`, like PhpUnit's `$this->markTestSkipped()`  

```php
#!/usr/bin/env php
<?php  
  
require_once "./vendor/autoload.php";
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    public function execute()
    {
        $this->runActions($this->getAvailableActions(), 'deployment');
    }
    
    public function prepare()
    {
    }
    
    public function deploy($env)
    {
        if ('prod' === $env) {
            $this->runAction('restart');
        }
    }
    
    public function restart()
    {
    }
    
    public function notify($env)
    {
        if ('prod' !== $env) {
             $this->skipAction('Production only');
        }
    }
}
  
\TestRecipe::run();

```
  
Command
-------
Recipe is a command with arguments and options.  
It is based on Symfony Console component.  
So you have `configure()` to define what input will be available.  
And same methods `addArgument()` and `addOption()`
You can use all Symfony default options:
  - --help to show info about command
  - -v, -vv, -vvv to make output more verbose
  - -q to disable output, except errors
  - -n to disable user input (non-interactive mode)

```php
<?php
  
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    protected function configure()
    {
        $this
            ->addArgument('argument', InputArgument::REQUIRED)
            ->addOption('option', null, InputOption::VALUE_REQUIRED);
    }
  
    public function doSmthAction(InputInterface $input, $argument, $option)
    {
        $argumentValue = $input->getArgument('argument');
        $optionValue = $input->getArgument('option');
        $argumentValue = $argument; // same
        $optionValue = $option; // same
    }
    
    public function doSmth2Action(OutputInterface $output)
    {
        $output->writeln('Hello');
    }
}
```
You can access defined inputs by it's names.  
  
There are few helper methods for user interations:  
To get true/false result `$this->askConfirmationQuestion()`  
To get choice from array of variants `$this->askChoiceQuestion()` (it results value, not key)  
To get string input `$this->askQuestion()`  
  
```php
<?php
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    public function askSmthAction()
    {
        $result = $this->askConfirmationQuestion('Are you sure?', false);
        
        $result = $this->askChoiceQuestion('Pick a color', ['red', 'green', 'blue'], 'green');
        
        $result = $this->askQuestion('Enter your name', 'anonymous');
    }
}
```
You can implement your own questions or override these.  

Autowired resources
-------------------
Resources are arguments to actions.  
You can use resource name or class name to wire existing resource to action call.  
Class and name can point to different resources, so result may be unpredictable. Please avoid these situations.  
  
Resources are (in decreasing priority order):  
  - get[A-Z] methods in recipe, some sort or dymanic properties
  - public non-static recipe properties
  - input arguments
  - input options
  - container services (internal)
  - container parameters (internal)
  
If there are resources with same names or class names, first occurence will be used.  
Snake_case and camelCase treat same.  
  
Usable container services:
  - input (Symfony\Component\Console\Input\InputInterface)
  - output (Symfony\Component\Console\Output\OutputInterface)
  - style (Symfony\Component\Console\Style\SymfonyStyle)
  - connection (Aes3xs\Tasker\Connection\Connection)
  - logger (Monolog\Logger)
  - runner (Aes3xs\Tasker\Runner\Runner)

And some helpers:
  - shell (Aes3xs\Tasker\Service\Shell)
  - composer (Aes3xs\Tasker\Service\Composer)
  - git (Aes3xs\Tasker\Service\Git)
  - releaser (Aes3xs\Tasker\Service\Releaser)
  - symfony (Aes3xs\Tasker\Service\Symfony)

```php
<?php

class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    /**
     * Can be obtained by:
     * dynamicRecipeResource
     * dynamic_recipe_resource
     */
    public function getDynamicRecipeResource($dependency)
    {
        return $dependency * 10;
    }
    
    /**
     * Can be obtained by:
     * SomeClass
     * recipePropertyObject
     * recipe_property_object
     */
    public $recipePropertyObject; // Contains \SomeClass
    
    /**
     * Can be obtained by:
     * recipeProperty
     * recipe_property
     */
    public $recipeProperty;
    
    /**
     * Can be obtained by:
     * recipePropertyCallback
     * recipe_property_callback
     */
    public $recipePropertyCallback;
    
    public function setupRecipeCallbackProperty()
    {
        // Callback arguments resolving same way as actions
        $this->recipePropertyCallback = function ($dependency) {
            return $dependency * 10;
        };
    }
    
    protected function configure()
    {
        /**
         * Can be obtained by:
         * inputArgument
         * input_argument
         */
        $this->addArgument('input_argument');
        
        /**
         * Can be obtained by:
         * inputOption
         * input_option
         */
        $this->addOption('input_option');
    }
    
    /**
     * Can be obtained by class or name
     */
    public function containerServicesAction(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Symfony\Component\Console\Style\SymfonyStyle $style,
        \Aes3xs\Tasker\Connection\Connection $connection,
        \Monolog\Logger $logger,
        \Aes3xs\Tasker\Runner\Runner $runner,
        \Aes3xs\Tasker\Service\Shell $shell,
        \Aes3xs\Tasker\Service\Composer $composer,
        \Aes3xs\Tasker\Service\Git $git,
        \Aes3xs\Tasker\Service\Releaser $releaser,
        \Aes3xs\Tasker\Service\Symfony $symfony
    ) {
        // Do smth
    }
}
```

Connection
----------

Simply set up connection parameters before using it.  
It initializes automatically on first call. Reuse or reconnect is not provided for now.  
  
Local connection is pretty straightforward. To use it leave host null.  

Remote connection supplied with three authentication types:  
  - login/password, use `setPassword('password')`
  - public key (optionally passphrase), use `setPublicKey($path or key itself)`
  - agent forwarding, use `setForwarding(true)`
  
Remote connection based on PhpSecLib.  

> Ssh extension was implemented, but disabled in case of broken forwarding.  
> When you are using ssh forwarding manually, there is environment variable $SSH_AUTH_SOCK, which contains path to agent socket.So you can continue using forwarding to connect to another server.  
> With php ssh extension this variable is missing. It's really necessary feature, so I switched back to PhpSecLib for now.   

```php
<?php
  
class TestRecipe extends \Aes3xs\Tasker\AbstractRecipe
{
    protected function connect(\Aes3xs\Tasker\Connection\Connection $connection)
    {
        // By default connection is local
        // Local means host is null, 127.0.0.1 or localhost
        
        // Remote connection
        $connection
            ->getParameters()
            ->setHost('192.168.1.1') // Default port 22
            ->setLogin('root')
            ->setPassword('password');
        
        $connection->exec('echo hello');
    }
}
```

Services
--------

### Shell

Shell is built on top of connection. So if you're already connected to remote (or local) server, you can also use Shell.  
It contains most usable shell commands:  
  - exec
  - ln
  - chmod
  - chown
  - rm
  - mkdir
  - touch
  - readlink
  - realpath
  - dirname
  - ls
  - which
  
Helper methods
  - exists()
  - isFile()
  - isDir()
  - isLink()
  - isWritable()
  - isReadable()
  - write()
  - read()
  - copy()
  - copyPaths()
  - linkPaths()
  - checkWritable()
  - checkReadable()
  
If you want to run all commands as another user, configure it with setUser(). So all commands will be prepended with `sudo -EHu USER bash -c "COMMAND"`. SSH agent forwarding will be also available.  
  
If you want to run all commands from specific directory, configure it with setCwd().  
  
These options take effect only on Shell service itself, not Connection.  
Other services (Releaser, Git, Composer, Symfony) also use Shell inside, so take it into account.  

### Releaser

Releaser manages releases. It prepares directory structure to store your releases and links them during deploy or rollback.  
First call setDeployPath() to point to root directory where all related stuff will be located.  
```
/var/www/project <- deploy_path
    │ 
    ├─ releases
    │   ├─ 20180101221100 (YmdHis format)
    │   ├─ 20180101221101
    │   ├─ 20180101221102
    │   ├─ 20180101221103
    │   ├─ 20180101221104 (Symfony example) <- current_path
    │   │   ├─ app
    │   │   │   └─ config
    │   │   │       └─ ~parameters.yml (symlink in shared)
    │   │   ├─ var
    │   │   │   └─ ~logs (symlink in shared)
    │   │   └─ release.lock (exists only in completed releases)
    │   │
    │   └─ 20180101221105 (deploy in progress...) <- release_path
    │     
    ├─ ~current (symlink to 20180101221104 for example)
    │
    ├─ shared
    │   ├─ app
    │   │   └─ config
    │   │       └─ parameters.yml
    │   └─ var
    │       └─ logs
    │           └─ ...
    │           
    └─ deploy.lock
```

Then call prepare() to build directory structure.  
Use lock(), unlock() and isLocked() to control deploy.lock file, use it to prevent simultaneous deploys.  
Call create() to create new release, release() to symlink as current and add release.lock file, link() to symlink specific existing release, rollback() to symlink previous, cleanup() to delete unused releases. Get all available releases with getReleaseList(), it shows only completed releases, broken and unexpected directories and files are ignored.    
Use updateReleaseShares() to update shared files and directories. Shares are same in all releases and they are stored separately.  
getCurrentReleasePath() to get path to currently linked release.  
getCurrentReleaseName() to get name of current release (dirname obviously).  

### Git

Preferred way to use private repositories is agent forwarding. But you can also setKeyPath() to use your public key.  
Methods:
  - checkout()
  - cloneAt()
  - log()
  - fetch()
  - getBranches()
  - getCurrentBranch()

### Composer
Methods:
  - install()
  - update()
  - download() to download phar archive

### Symfony
First set path to console setConsolePath(), usually release_path/bin/console
  - setEnv()
  - setDebug()
  - setInteractive()
  - runCommand()

Pass arguments and options to runCommand(). Options is an associative array, key is option name (if value is null, option treats as a flag).  

