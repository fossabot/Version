<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Command;

use SoureCode\Version\Configuration\Configuration;
use SoureCode\Version\Configuration\VersionConfiguration;
use SoureCode\Version\Exception\RuntimeException;
use SoureCode\Version\Loader\JsonFileLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractVersionCommand.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractVersionCommand extends Command
{
    protected function getConfiguration(): Configuration
    {
        $configurationDirectories = [
            getcwd(),
        ];

        $homeDirectory = $this->homeDirectory();

        if ($homeDirectory) {
            $configurationDirectories[] = $homeDirectory;
        }

        $fileLocator = new FileLocator($configurationDirectories);

        /**
         * @var string[] $configurationFiles
         */
        $configurationFiles = $fileLocator->locate('version.json', null, false);
        $loaderResolver = new LoaderResolver([new JsonFileLoader($fileLocator)]);
        $configurations = [];

        foreach ($configurationFiles as $configurationFile) {
            $loader = $loaderResolver->resolve($configurationFile);

            if (!$loader) {
                throw new RuntimeException(sprintf('Missing loader for configuration "%s".', $configurationFile));
            }

            $configurations[] = $loader->load($configurationFile);
        }

        $processor = new Processor();
        $versionConfiguration = new VersionConfiguration();

        $configuration = $processor->processConfiguration($versionConfiguration, $configurations);

        return new Configuration($configuration);
    }

    private function homeDirectory(): ?string
    {
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');
        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }

        return empty($home) ? null : $home;
    }
}
