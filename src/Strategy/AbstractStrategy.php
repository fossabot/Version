<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Strategy;

use SoureCode\SemanticVersion\Version;
use SoureCode\Version\Configuration\Configuration;
use Symfony\Component\Finder\Finder;

/**
 * Class AbstractStrategy.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractStrategy implements StrategyInterface
{
    protected array $options;

    protected Configuration $configuration;

    public function __construct(Configuration $configuration, array $options)
    {
        $this->configuration = $configuration;
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    protected function findFiles()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(getcwd())
            ->path($this->options['path'])
            ->notPath($this->options['notPath'])
            ->name($this->options['name'])
            ->notName($this->options['notName'])
            ->exclude(['vendor', 'node_modules']);

        return $finder;
    }

    abstract public function apply(Version $version): void;
}
