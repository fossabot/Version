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

use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function array_map;
use function implode;
use function in_array;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback;
use SoureCode\SemanticVersion\Version;
use SoureCode\Version\Exception\InvalidArgumentException;
use function sprintf;
use function strtoupper;

/**
 * Class Pattern.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Pattern
{
    private static ?array $placeholderMapping = null;

    private string $pattern;

    private string $expression;

    private array $matches = [];

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
        $this->createExpression();
    }

    private function createExpression(): void
    {
        $escapedPattern = preg_quote($this->pattern, '/');
        $escapedPattern = preg_replace('/\\\{([A-Z]+)\\\}/', '{$1}', $escapedPattern);
        $placeholder = static::getPlaceholderMapping();
        $expression = preg_replace_callback(
            '/{([A-Z]+)}/',
            function (array $matches) use ($placeholder) {
                $match = $matches[1];

                if (!array_key_exists($match, $placeholder)) {
                    throw new InvalidArgumentException('Invalid pattern.');
                }

                $this->matches[] = $match;

                return $placeholder[$match];
            },
            $escapedPattern
        );

        if (null === $expression) {
            throw new InvalidArgumentException('Invalid pattern.');
        }

        $this->expression = '/'.$expression.'/';
    }

    /**
     * @return array<string, string>
     */
    public static function getPlaceholderMapping(): array
    {
        if (!static::$placeholderMapping) {
            static::$placeholderMapping = [
                'MAIN' => sprintf(
                    "%s\.%s\.%s",
                    Version::$majorExpression,
                    Version::$minorExpression,
                    Version::$patchExpression
                ),
                'MAJOR' => Version::$majorExpression,
                'MINOR' => Version::$minorExpression,
                'PATCH' => Version::$patchExpression,
                'PRERELEASE' => Version::$preReleaseExpression,
                'BUILDMETADATA' => Version::$buildMetadataExpression,
                'VERSION' => Version::getExpression(),
            ];
        }

        return static::$placeholderMapping;
    }

    public function format(Version $version): string
    {
        return preg_replace(
            [
                '/{VERSION}/',
                '/{MAIN}/',
                '/{MAJOR}/',
                '/{MINOR}/',
                '/{PATCH}/',
                '/{PRERELEASE}/',
                '/{BUILDMETADATA}/',
            ],
            [
                (string) $version,
                sprintf('%d.%d.%d', $version->getMajor(), $version->getMinor(), $version->getPatch()),
                $version->getMajor(),
                $version->getMinor(),
                $version->getPatch(),
                implode('.', $version->getPreRelease()),
                implode('.', $version->getBuildMetadata()),
            ],
            $this->pattern
        );
    }

    public function match(string $value): bool
    {
        $result = preg_match($this->expression, $value);

        return false !== $result && 0 !== $result;
    }

    public function extract(string $value): ?array
    {
        if (preg_match($this->expression, $value, $matches)) {
            $matches = $this->extractNamedGroups($matches);

            return $this->fixNumericKeys($matches);
        }

        return null;
    }

    /**
     * @param string[] $matches
     */
    private function extractNamedGroups(array $matches): array
    {
        $keys = array_flip(array_map('strtolower', array_keys(static::getPlaceholderMapping())));

        return array_intersect_key($matches, $keys);
    }

    /**
     * @param string[] $matches
     *
     * @return (int|string)[]
     */
    private function fixNumericKeys(array $matches): array
    {
        $numericKeys = ['major', 'minor', 'patch'];

        foreach ($numericKeys as $numericKey) {
            if (array_key_exists($numericKey, $matches)) {
                $matches[$numericKey] = (int) ($matches[$numericKey]);
            }
        }

        return $matches;
    }

    public function containsVersion(): bool
    {
        return $this->contains('VERSION');
    }

    public function contains(string $part): bool
    {
        return in_array(strtoupper($part), $this->matches, true);
    }

    public function containsMain(): bool
    {
        return $this->contains('MAIN');
    }

    public function containsMajor(): bool
    {
        return $this->contains('MAJOR');
    }

    public function containsMinor(): bool
    {
        return $this->contains('MINOR');
    }

    public function containsPatch(): bool
    {
        return $this->contains('PATCH');
    }

    public function containsPreRelease(): bool
    {
        return $this->contains('PRERELEASE');
    }

    public function containsBuildMetadata(): bool
    {
        return $this->contains('BUILDMETADATA');
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function __toString()
    {
        return $this->pattern;
    }
}
