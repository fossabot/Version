<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetCommand.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class GetCommand extends AbstractVersionCommand
{
    protected static $defaultName = 'get';

    protected function configure()
    {
        $this->setName('get')
            ->setDescription('Prints the current version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getConfiguration();
        $version = $configuration->getVersion();

        $output->writeln($version);

        return Command::SUCCESS;
    }
}
