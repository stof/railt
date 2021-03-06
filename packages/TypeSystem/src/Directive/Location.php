<?php

/**
 * This file is part of GraphQL TypeSystem package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Railt\TypeSystem\Directive;

/**
 * Class Location
 */
final class Location
{
    /**#@+
     * Request Definitions
     *
     * @var string
     */
    public const QUERY               = 'QUERY';
    public const MUTATION            = 'MUTATION';
    public const SUBSCRIPTION        = 'SUBSCRIPTION';
    public const FIELD               = 'FIELD';
    public const FRAGMENT_DEFINITION = 'FRAGMENT_DEFINITION';
    public const FRAGMENT_SPREAD     = 'FRAGMENT_SPREAD';
    public const INLINE_FRAGMENT     = 'INLINE_FRAGMENT';
    public const VARIABLE_DEFINITION = 'VARIABLE_DEFINITION';
    /**#@-*/

    /**#@+
     * Type System Definitions
     *
     * @var string
     */
    public const SCHEMA                 = 'SCHEMA';
    public const SCALAR                 = 'SCALAR';
    public const OBJECT                 = 'OBJECT';
    public const FIELD_DEFINITION       = 'FIELD_DEFINITION';
    public const ARGUMENT_DEFINITION    = 'ARGUMENT_DEFINITION';
    public const INTERFACE              = 'INTERFACE';
    public const UNION                  = 'UNION';
    public const ENUM                   = 'ENUM';
    public const ENUM_VALUE             = 'ENUM_VALUE';
    public const INPUT_OBJECT           = 'INPUT_OBJECT';
    public const INPUT_FIELD_DEFINITION = 'INPUT_FIELD_DEFINITION';
    /**#@-*/

    /**
     * @var array|string[]|null
     */
    protected static ?array $memoized = null;

    /**
     * @param string $location
     * @return bool
     */
    public static function has(string $location): bool
    {
        return \in_array($location, self::all(), true);
    }

    /**
     * @return array|string[]
     */
    public static function all(): array
    {
        if (self::$memoized === null) {
            try {
                self::$memoized = static::readValues();
            } catch (\ReflectionException $e) {
                return [];
            }
        }

        return self::$memoized;
    }

    /**
     * @return array|string[]
     * @throws \ReflectionException
     */
    protected static function readValues(): array
    {
        $values = (new \ReflectionClass(static::class))->getConstants();

        return \array_values($values);
    }
}
