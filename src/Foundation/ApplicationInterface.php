<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Foundation;

use Railt\Container\ContainerInterface;
use Railt\Foundation\Config\ConfigurationInterface;
use Railt\Io\Readable;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Interface ApplicationInterface
 */
interface ApplicationInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @param ConfigurationInterface $config
     * @return ApplicationInterface
     */
    public function configure(ConfigurationInterface $config): self;

    /**
     * @return ConsoleApplication
     */
    public function getConsoleApplication(): ConsoleApplication;

    /**
     * @param string $extension
     * @return ApplicationInterface
     */
    public function extend(string $extension): self;

    /**
     * @param Readable $schema
     * @return ConnectionInterface
     */
    public function connect(Readable $schema): ConnectionInterface;

    /**
     * @return bool
     */
    public function isDebug(): bool;
}