<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Abstraction\Common;

use Railt\Reflection\Abstraction\CalleeDirectiveInterface;

/**
 * Interface HasDirectivesInterface
 * @package Railt\Reflection\Abstraction\Common
 */
interface HasDirectivesInterface
{
    /**
     * @return iterable|CalleeDirectiveInterface[]
     */
    public function getDirectives(): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function hasDirective(string $name): bool;

    /**
     * @param string $name
     * @return null|CalleeDirectiveInterface
     */
    public function getDirective(string $name): ?CalleeDirectiveInterface;
}