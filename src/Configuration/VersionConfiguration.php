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
use SoureCode\Version\Configuration\Strategy\ComposerStrategyConfiguration;
use SoureCode\Version\Configuration\Strategy\ExpressionStrategyConfiguration;
use SoureCode\Version\Configuration\Strategy\NodeStrategyConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class VersionConfiguration.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class VersionConfiguration extends AbstractConfiguration implements ConfigurationInterface
{
    private static array $strategies = [
        'composer' => ComposerStrategyConfiguration::class,
        'node' => NodeStrategyConfiguration::class,
        'expression' => ExpressionStrategyConfiguration::class,
    ];

    private ?Processor $processor = null;

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('version');

        $root = $treeBuilder->getRootNode();

        //@formatter:off
        $root
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('version')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifString()
                        ->then(function ($value) {
                            return Version::fromString($value);
                        })
                    ->end()
                ->end()
                ->arrayNode('strategies')
                    ->requiresAtLeastOneElement()
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->isRequired()
                        ->ignoreExtraKeys(false)
                        ->children()
                            ->enumNode('type')
                                ->values(array_keys(static::$strategies))
                                ->isRequired()
                            ->end()
                        ->end()
                        ->validate()
                            ->always(function ($value) {
                                $type = $value['type'];

                                $class = static::getStrategyConfigurationClass($type);
                                $configurationDefinition = new $class();

                                return $this->getProcessor()->processConfiguration($configurationDefinition, [$value]);
                            })
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        //@formatter:on

        $this->addPattern($root->children(), 'branch_pattern', 'release/{MAJOR}.{MINOR}');
        $this->addPattern($root->children(), 'tag_pattern', 'v{MAJOR}.{MINOR}.{PATCH}');

        return $treeBuilder;
    }

    private static function getStrategyConfigurationClass(string $type)
    {
        if (!array_key_exists($type, static::$strategies)) {
            throw new InvalidConfigurationException(sprintf('The strategy %s is invalid', $type));
        }

        return static::$strategies[$type];
    }

    private function getProcessor()
    {
        if (!$this->processor) {
            $this->processor = new Processor();
        }

        return $this->processor;
    }
}
