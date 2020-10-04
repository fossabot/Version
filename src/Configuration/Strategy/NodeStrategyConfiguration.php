<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Configuration\Strategy;

use SoureCode\Version\Configuration\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class NodeStrategyConfiguration.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class NodeStrategyConfiguration extends AbstractConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('node');

        /**
         * @var ArrayNodeDefinition $root
         */
        $root = $treeBuilder->getRootNode();
        $root->addDefaultsIfNotSet();
        $children = $root->children();

        //@formatter:off
        $children
            ->scalarNode('type')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function (string $value) {
                        return 'node' !== $value;
                    })
                    ->thenInvalid('Invalid strategy configuration type.');

        $children->enumNode('tool')
            ->values(['npm', 'yarn'])
            ->isRequired()
            ->cannotBeEmpty()
            ->defaultValue('npm');

        $children->scalarNode('directory')
            ->cannotBeEmpty()
            ->defaultValue(getcwd())
            ->validate()
                ->always(function (string $value) {
                    if (!file_exists($value) || !is_dir($value)) {
                        throw new InvalidConfigurationException(sprintf('The directory "%s" is invalid.', $value));
                    }
                })
        ;
        //@formatter:on

        $this->addPattern($children, 'pattern', '{VERSION}');

        return $treeBuilder;
    }
}
