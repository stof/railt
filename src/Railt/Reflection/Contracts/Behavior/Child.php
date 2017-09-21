<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Reflection\Contracts\Behavior;

/**
 * Interface Child
 * @package Railt\Reflection\Contracts\Behavior
 */
interface Child
{
    /**
     * @return null|Nameable
     */
    public function getParent(): ?Nameable;

    /**
     * @param Child $type
     * @return bool
     */
    public function isSibling(Child $type): bool;
}
