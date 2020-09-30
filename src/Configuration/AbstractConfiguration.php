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

use SoureCode\Version\Pattern;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Class AbstractConfiguration.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractConfiguration
{
    protected function addPattern(NodeBuilder $builder, string $name, string $default = null)
    {
        //@formatter:off
        $node = $builder
            ->scalarNode($name)
                ->cannotBeEmpty()
                ->validate()
                    ->always()
                    ->then(function ($value) {
                        return new Pattern($value);
                    })
                ->end()
        ;
        //@formatter:on

        if ($default) {
            $node->defaultValue(new Pattern($default));
        }

        return $node;
    }

    protected function addFindFiles(NodeBuilder $builder)
    {
        //@formatter:off
        $builder
            ->arrayNode('path')
                ->requiresAtLeastOneElement()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return [$value];
                    })
                ->end()
                ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->arrayNode('notPath')
                ->requiresAtLeastOneElement()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return [$value];
                    })
                ->end()
                ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->arrayNode('name')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return [$value];
                    })
                ->end()
                ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
            ->arrayNode('notName')
                ->requiresAtLeastOneElement()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($value) {
                        return [$value];
                    })
                ->end()
                ->scalarPrototype()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        //@formatter:on
    }
}
