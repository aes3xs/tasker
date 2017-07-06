<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Tests\Deploy;

use Aes3xs\Yodler\Connection\Connection;
use Aes3xs\Yodler\Deploy\DeployBuilder;

class DeployBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../Fixtures/recipe/DefaultRecipe.php';
    }

    protected function assertConnection($connection, $host, $port, $login, $password, $key, $passphrase, $forwarding)
    {
        /** @var Connection $connection */

        $this->assertInstanceOf(Connection::class, $connection);

        $this->assertEquals($host, $connection->getHost());
        $this->assertEquals($port, $connection->getPort());
        $this->assertEquals($login, $connection->getLogin());
        $this->assertEquals($password, $connection->getPassword());
        $this->assertEquals($key, $connection->getKey());
        $this->assertEquals($passphrase, $connection->getPassphrase());
        $this->assertEquals($forwarding, $connection->isForwarding());
    }

    public function testCreateListFromConfiguration()
    {
        $configuration = [
            'scenario'   => \DefaultRecipe::class,
            'connection' => [
                'host'       => 'host',
                'port'       => 1122,
                'login'      => 'login',
                'password'   => 'password',
                'key'        => 'key',
                'passphrase' => 'passphrase',
                'forwarding' => true,
            ],
            'parameters' => [],
        ];

        $builder = new DeployBuilder();

        $deploy = $builder->build('test', $configuration);

        $this->assertConnection($deploy->getConnection(), 'host', 1122, 'login', 'password', 'key', 'passphrase', true);
    }

    public function testLoadKey()
    {
        $configuration = [
            'scenario'   => \DefaultRecipe::class,
            'connection' => [
                'host'       => null,
                'port'       => null,
                'login'      => null,
                'password'   => null,
                'passphrase' => null,
                'forwarding' => true,
                'key'        => __DIR__ . '/../Fixtures/key/id_rsa'
            ],
            'parameters' => [],
        ];

        $builder = new DeployBuilder($configuration);

        $deploy = $builder->build('test', $configuration);

        $expected = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
Private Key Contents
-----END RSA PRIVATE KEY-----
EOF;

        $this->assertEquals($expected, $deploy->getConnection()->getKey());
    }
}
