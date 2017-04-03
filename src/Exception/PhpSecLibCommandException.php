<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Exception;

/**
 * This exception is thrown when phpseclib commander command was failed.
 */
class PhpSecLibCommandException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor.
     *
     * @param string $command
     */
    public function __construct($command)
    {
        $this->command = $command;

        parent::__construct(sprintf('Error occured while executing command "%s"', $command));
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Add error description string or array of errors.
     *
     * @param $error
     */
    public function addError($error)
    {
        if (!$error) {
            return;
        }

        if (is_array($error)) {
            $this->errors = array_merge($this->errors, $error);
        } else {
            $this->errors[] = (string) $error;
        }

        $this->message = sprintf('Error occured while executing command "%s" %s',
            $this->command,
            PHP_EOL . 'Errors: ' . PHP_EOL . implode(PHP_EOL, $this->errors)
        );
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
