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
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * Class AbstractConfiguration.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractConfiguration
{
    protected function addPattern(NodeBuilder $builder, string $name, string $default = null): ScalarNodeDefinition
    {
        //@formatter:off
        $node = $builder->scalarNode($name);
        $node->cannotBeEmpty();
        $node->validate()
            ->always()
            ->then(function (string $value) {
                return new Pattern($value);
            })
        ;
        //@formatter:on

        if ($default) {
            $node->defaultValue(new Pattern($default));
        }

        return $node;
    }

    protected function addFindFiles(NodeBuilder $builder): void
    {
        //@formatter:off
        $pathNode = $builder->arrayNode('path');
        $pathNode->requiresAtLeastOneElement();
        $pathNode->beforeNormalization()
            ->ifString()
            ->then(function (string $value) {
                return [$value];
            });
        $pathNode->scalarPrototype()
            ->cannotBeEmpty();

        $notPathNode = $builder->arrayNode('notPath');
        $notPathNode->requiresAtLeastOneElement();
        $notPathNode->beforeNormalization()
            ->ifString()
            ->then(function (string $value) {
                return [$value];
            });
        $notPathNode->scalarPrototype()
            ->cannotBeEmpty();

        $nameNode = $builder->arrayNode('name');
        $nameNode->isRequired();
        $nameNode->requiresAtLeastOneElement();
        $nameNode->beforeNormalization()
            ->ifString()
            ->then(function (string $value) {
                return [$value];
            });
        $nameNode->scalarPrototype()
            ->cannotBeEmpty();

        $notNameNode = $builder->arrayNode('notName');
        $notNameNode->requiresAtLeastOneElement();
        $notNameNode->beforeNormalization()
            ->ifString()
            ->then(function (string $value) {
                return [$value];
            });
        $notNameNode->scalarPrototype()
            ->cannotBeEmpty()
        ;
        //@formatter:on
    }
}
