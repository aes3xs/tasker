<?php

/*
 * This file is part of the Yodler package.
 *
 * (c) aes3xs <aes3xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aes3xs\Yodler\Recipe;

use Aes3xs\Yodler\Commander\CommanderInterface;
use Aes3xs\Yodler\Connection\Connection;

class GitRecipe implements RecipeInterface
{
    public $repository;
    public $branch;
    public $tag;
    public $cloneReference;
    public $clonePath;

    public function git(CommanderInterface $commander, Connection $connection)
    {
        $git = $commander->exec('which git');
        if (!$git) {
            throw new \RuntimeException('Git not found');
        }
        $connection->getVariables()->add('git', $git);
        return $git;
    }

    public function gitCache(CommanderInterface $commander, $git, Connection $connection)
    {
        $gitVersion = $commander->exec("$git version");
        $regs = [];
        if (preg_match('/((\d+\.?)+)/', $gitVersion, $regs)) {
            $version = $regs[1];
        } else {
            $version = "1.0.0";
        }
        $useCache = version_compare($version, '2.3', '>=');
        $connection->getVariables()->add('gitCache', $useCache);
        return $useCache;
    }

    public function updateCode(CommanderInterface $commander, $repository, $branch, $tag, $git, $gitCache, $cloneReference, $clonePath)
    {
        $depth = $gitCache ? '' : '--depth 1';

        $at = '';
        if ($branch) {
            $at = "-b $branch";
        }
        if ($tag) {
            $at = "-b $tag";
        }

        if ($gitCache && $cloneReference) {
            try {
                $commander->exec("$git clone $at --recursive -q --reference $cloneReference --dissociate $repository $clonePath 2>&1");
            } catch (\RuntimeException $exc) {
                // If cloneReference has a failed git clone, is empty, shallow etc, git would throw error and give up. So we're forcing it to act without reference in this situation
                $commander->exec("$git clone $at --recursive -q $repository $clonePath 2>&1");
            }
        } else {
            // if we're using git cache this would be identical to above code in catch - full clone. If not, it would create shallow clone.
            $commander->exec("$git clone $at $depth --recursive -q $repository $clonePath 2>&1");
        }
    }



}
