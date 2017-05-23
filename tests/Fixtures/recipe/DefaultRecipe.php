<?php

class DefaultRecipe
{
    public function __construct()
    {
    }

    public function test(\Aes3xs\Yodler\Service\Shell $shell)
    {
        return implode(PHP_EOL, $shell->ls(__DIR__));
    }
}
