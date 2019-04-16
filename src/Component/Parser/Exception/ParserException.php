<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Component\Parser\Exception;

use Railt\Component\Io\Exception\ExternalFileException;

/**
 * Class ParserException
 */
abstract class ParserException extends ExternalFileException
{
}