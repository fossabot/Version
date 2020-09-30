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
use SoureCode\Version\Pattern;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ExpressionStrategy.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ExpressionStrategy extends AbstractStrategy
{
    public function apply(Version $version): void
    {
        /**
         * @var Pattern $replacementPattern
         */
        $replacementPattern = $this->options['replacement'];
        $replacement = $replacementPattern->format($version);
        $filesystem = new Filesystem();

        $placeholder = Pattern::getPlaceholderMapping();

        $expression = preg_replace(
            array_map(
                function ($value) {
                    return sprintf('/{%s}/', $value);
                },
                array_keys($placeholder)
            ),
            array_values($placeholder),
            $this->options['expression']
        );

        $finder = $this->findFiles();

        foreach ($finder as $file) {
            $contents = $file->getContents();
            $contents = preg_replace($expression, $replacement, $contents);
            $filesystem->dumpFile($file->getPathname(), $contents);
        }
    }
}
