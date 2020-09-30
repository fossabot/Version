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
use SoureCode\Version\Exception\RuntimeException;
use SoureCode\Version\Pattern;
use Symfony\Component\Process\Process;

/**
 * Class NodeStrategy.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class NodeStrategy extends AbstractStrategy
{
    public function apply(Version $version): void
    {
        /**
         * @var Pattern $pattern
         */
        $pattern = $this->options['pattern'];
        $directory = $this->options['directory'];
        $value = $pattern->format($version);

        $command = array_merge($this->getCommand(), [$value]);

        $process = new Process($command, $directory);
        $process->mustRun();
    }

    private function getCommand(): array
    {
        $tool = $this->options['tool'];

        if ('npm' === $tool) {
            return ['npm', 'version', '--no-git-tag-version', '--allow-same-version'];
        } elseif ('yarn' === $tool) {
            return ['yarn', 'version', '--no-git-tag-version', '--no-commit-hooks', '--new-version'];
        }

        throw new RuntimeException(sprintf('The tool "%s" is invalid.', $tool));
    }
}
