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
use Symfony\Component\Process\Process;

/**
 * Class ComposerStrategy.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ComposerStrategy extends AbstractStrategy
{
    public function apply(Version $version): void
    {
        /**
         * @var Pattern $pattern
         */
        $pattern = $this->options['pattern'];
        $directory = $this->options['directory'];

        $value = $pattern->format($version);

        $process = new Process(['composer', 'config', 'extra.branch-alias.dev-master', $value], $directory);
        $process->mustRun();
    }
}
