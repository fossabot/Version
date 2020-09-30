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

/**
 * Interface StrategyInterface.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface StrategyInterface
{
    public function apply(Version $version): void;

    public function getOptions(): array;
}
