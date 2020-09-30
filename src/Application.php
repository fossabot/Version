<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version;

use SoureCode\Version\Command\GetCommand;
use SoureCode\Version\Command\SetCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
final class Application extends BaseApplication
{
    public const VERSION = '0.1.0-DEV';

    public function __construct()
    {
        parent::__construct('version', self::VERSION);

        $this->add(new GetCommand());
        $this->add(new SetCommand());
    }
}
