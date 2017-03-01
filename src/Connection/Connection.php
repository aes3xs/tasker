<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Connection;

use Aes3xs\Yodler\Variable\VariableListInterface;
use Aes3xs\Yodler\Variable\VariableSuppliedInterface;

/**
 * Connection definition implementation.
 */
class Connection implements ConnectionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ServerInterface
     */
    protected $server;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var VariableListInterface
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param $name
     * @param ServerInterface $server
     * @param UserInterface $user
     * @param VariableListInterface $variables
     */
    public function __construct($name, ServerInterface $server, UserInterface $user, VariableListInterface $variables)
    {
        $this->name = $name;
        $this->server = $server;
        $this->user = $user;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
