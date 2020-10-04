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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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

        /**
         * @var ArrayNodeDefinition $root
         */
        $root = $treeBuilder->getRootNode();

        /**
         * @var NodeBuilder $children
         */
        $children = $root->children();
        $root->addDefaultsIfNotSet();

        //@formatter:off
        $children
            ->scalarNode('version')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifString()
                    ->then(function (string $value) {
                        return Version::fromString($value);
                    });

        $strategiesNode = $children->arrayNode('strategies');

        $strategiesNode->requiresAtLeastOneElement();
        $strategiesNode->defaultValue([]);
        $strategiesNode->arrayPrototype()
            ->isRequired()
            ->ignoreExtraKeys(false)
            ->children()
                ->enumNode('type')
                    ->values(array_keys(static::$strategies))
                    ->isRequired();

        $strategiesNode->validate()
            ->always(function (array $value) {
                $type = $value['type'];

                $class = static::getStrategyConfigurationClass($type);
                $configurationDefinition = new $class();

                return $this->getProcessor()->processConfiguration($configurationDefinition, [$value]);
            })
        ;
        //@formatter:on

        $this->addPattern($children, 'branch_pattern', 'release/{MAJOR}.{MINOR}');
        $this->addPattern($children, 'tag_pattern', 'v{MAJOR}.{MINOR}.{PATCH}');

        return $treeBuilder;
    }

    /**
     * @template T of ConfigurationInterface
     * @psalm-return class-string<T>
     */
    private static function getStrategyConfigurationClass(string $type): string
    {
        if (!array_key_exists($type, static::$strategies)) {
            throw new InvalidConfigurationException(sprintf('The strategy %s is invalid', $type));
        }

        return static::$strategies[$type];
    }

    private function getProcessor(): Processor
    {
        if (!$this->processor) {
            $this->processor = new Processor();
        }

        return $this->processor;
    }
}
