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

    public function testCreateListFromConfiguration()
    {
        $configuration = [
            'scenario'   => \DefaultRecipe::class,
            'connection' => [
                'host'        => 'host',
                'port'        => 1122,
                'login'       => 'login',
                'password'    => 'password',
                'public_key'  => 'public_key',
                'private_key' => 'private_key',
                'passphrase'  => 'passphrase',
                'forwarding'  => true,
            ],
            'parameters' => [],
        ];

        $builder = new DeployBuilder();

        $deploy = $builder->build('test', $configuration);
        $connection = $deploy->getConnection();

        /** @var Connection $connection */

        $this->assertInstanceOf(Connection::class, $connection);

        $this->assertEquals('host', $connection->getHost());
        $this->assertEquals(1122, $connection->getPort());
        $this->assertEquals('login', $connection->getLogin());
        $this->assertEquals('password', $connection->getPassword());
        $this->assertEquals('public_key', $connection->getPublicKey());
        $this->assertEquals('private_key', $connection->getPrivateKey());
        $this->assertEquals('passphrase', $connection->getPassphrase());
        $this->assertEquals(true, $connection->isForwarding());
    }

    public function testLoadKey()
    {
        $configuration = [
            'scenario'   => \DefaultRecipe::class,
            'connection' => [
                'host'        => null,
                'port'        => null,
                'login'       => null,
                'password'    => null,
                'passphrase'  => null,
                'forwarding'  => true,
                'public_key'  => __DIR__ . '/../Fixtures/key/id_rsa.pub',
                'private_key' => __DIR__ . '/../Fixtures/key/id_rsa',
            ],
            'parameters' => [],
        ];

        $builder = new DeployBuilder($configuration);

        $deploy = $builder->build('test', $configuration);

        $expected = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
Public Key Contents
-----END RSA PRIVATE KEY-----
EOF;

        $this->assertEquals($expected, $deploy->getConnection()->getPublicKey());

        $expected = <<<EOF
-----BEGIN RSA PRIVATE KEY-----
Private Key Contents
-----END RSA PRIVATE KEY-----
EOF;

        $this->assertEquals($expected, $deploy->getConnection()->getPrivateKey());
    }
}
