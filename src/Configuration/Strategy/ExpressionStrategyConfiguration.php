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
 * Class ExpressionStrategyConfiguration.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ExpressionStrategyConfiguration extends AbstractConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('expression');

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
                        return 'expression' !== $value;
                    })
                    ->thenInvalid('Invalid strategy configuration type.');

        $children->scalarNode('expression')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->always(function (string $value) {
                        /*
                         * @var mixed $value
                         */
                        if (false === @preg_match($value, '')) {
                            throw new InvalidConfigurationException(sprintf('The expression "%s" is invalid.', $value));
                        }

                        return $value;
                    })
        ;
        //@formatter:on

        $this->addPattern($children, 'replacement')->isRequired();
        $this->addFindFiles($children);

        return $treeBuilder;
    }
}
