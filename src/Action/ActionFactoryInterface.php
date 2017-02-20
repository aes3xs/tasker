<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Action;

/**
 * Interface to action factory.
 */
interface ActionFactoryInterface
{
    /**
     * Create action by array of data.
     *
     * @param array $data
     *
     * @return ActionInterface
     */
    public function create(array $data);

    /**
     * Create empty action list.
     *
     * @return ActionListInterface
     */
    public function createList();

    /**
     * Create list from configuration parsed from YAML.
     *
     * @param $actionsConfiguration
     *
     * @return ActionListInterface
     */
    public function createListFromConfiguration($actionsConfiguration);
}
