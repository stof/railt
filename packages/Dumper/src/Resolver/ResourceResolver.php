<?php

/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Railt\Dumper\Resolver;

/**
 * Class ResourceResolver
 */
class ResourceResolver extends Resolver
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function match($value): bool
    {
        return \is_resource($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function type($value): string
    {
        return \get_resource_type($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function value($value): string
    {
        return (string)(int)$value;
    }
}
