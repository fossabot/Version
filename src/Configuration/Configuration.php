<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Configuration;

use function array_key_exists;
use SoureCode\SemanticVersion\Version;
use SoureCode\Version\Exception\InvalidArgumentException;
use SoureCode\Version\Pattern;
use SoureCode\Version\Strategy\ComposerStrategy;
use SoureCode\Version\Strategy\ExpressionStrategy;
use SoureCode\Version\Strategy\NodeStrategy;
use SoureCode\Version\Strategy\StrategyInterface;

/**
 * Class VersionFile.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Configuration
{
    private static array $strategyMapping = [
        'composer' => ComposerStrategy::class,
        'node' => NodeStrategy::class,
        'expression' => ExpressionStrategy::class,
    ];

    /**
     * @var StrategyInterface[]
     */
    private ?array $strategies = null;

    private array $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return object[]
     */
    public function getStrategies(): array
    {
        if (!$this->strategies) {
            $this->strategies = [];

            if (array_key_exists('strategies', $this->configuration)) {
                foreach ($this->configuration['strategies'] as $key => $strategy) {
                    $type = $strategy['type'];
                    $class = static::getStrategyClass($type);
                    $this->strategies[] = new $class($this, $strategy);
                }
            }
        }

        return $this->strategies;
    }

    /**
     * @template T of StrategyInterface
     *
     * @psalm-return class-string<T>
     */
    private static function getStrategyClass(string $type): string
    {
        if (!array_key_exists($type, static::$strategyMapping)) {
            throw new InvalidArgumentException(sprintf('The strategy "%s" is invalid', $type));
        }

        return static::$strategyMapping[$type];
    }

    public function getVersion(): Version
    {
        return $this->configuration['version'];
    }

    public function getBranchPattern(): Pattern
    {
        return $this->configuration['branch_pattern'];
    }

    public function getTagPattern(): Pattern
    {
        return $this->configuration['tag_pattern'];
    }
}
