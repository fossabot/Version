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

use phpDocumentor\Reflection\Types\Null_;
use function get_class;
use function is_array;
use SoureCode\SemanticVersion\Version;
use SoureCode\Version\Exception\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SetCommand.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class SetCommand extends AbstractVersionCommand
{
    protected static $defaultName = 'set';

    protected function configure(): void
    {
        $this->setName('set')
            ->setDescription('Set the current version')
            ->addArgument('version', InputArgument::REQUIRED, 'The version');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $version = $input->getArgument('version');

        if (!$version) {
            $io = new SymfonyStyle($input, $output);

            $version = $io->ask(
                'Version?',
                null,
                function (string $value) {
                    return Version::fromString($value);
                }
            );

            $input->setArgument('version', (string) $version);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputVersion = $input->getArgument('version');

        if (!is_string($inputVersion)) {
            throw new InvalidArgumentException(sprintf('The argument "%s" is invalid.', json_encode($inputVersion)));
        }

        $version = Version::fromString($inputVersion);
        $configuration = $this->getConfiguration();
        $strategies = $configuration->getStrategies();

        $io->writeln(sprintf('version: %s', (string) $version), OutputInterface::VERBOSITY_VERBOSE);

        foreach ($strategies as $strategy) {
            $io->writeln(
                sprintf('strategy: %s', get_class($strategy)),
                OutputInterface::VERBOSITY_VERBOSE
            );

            if ($output->isVeryVerbose()) {
                $rows = [];

                foreach ($strategy->getOptions() as $key => $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $rows[] = [$key, $value];
                }

                $io->table(['Key', 'Value'], $rows);
            }

            $strategy->apply($version);
        }

        return Command::SUCCESS;
    }
}
