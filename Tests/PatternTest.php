<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Tests;

use PHPUnit\Framework\TestCase;
use SoureCode\SemanticVersion\Version;
use SoureCode\Version\Pattern;

/**
 * Class PatternTest.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class PatternTest extends TestCase
{
    public function testFormat()
    {
        $pattern = new Pattern('fez{MAIN}foo{MAJOR}bar{MINOR}baz{PATCH}boz{PRERELEASE}faz{BUILDMETADATA}bez');
        $version = Version::fromString('1.2.3-ALPHA.BETA+SHA.2');

        static::assertSame('fez1.2.3foo1bar2baz3bozALPHA.BETAfazSHA.2bez', $pattern->format($version));
    }

    public function testContainsMajor()
    {
        $pattern = new Pattern('foo{MAJOR}bar');

        static::assertFalse($pattern->containsMain());
        static::assertTrue($pattern->containsMajor());
        static::assertFalse($pattern->containsMinor());
        static::assertFalse($pattern->containsPatch());
        static::assertFalse($pattern->containsPreRelease());
        static::assertFalse($pattern->containsBuildMetadata());
    }

    public function testContainsPatch()
    {
        $pattern = new Pattern('foo{PATCH}bar');

        static::assertFalse($pattern->containsMain());
        static::assertFalse($pattern->containsMajor());
        static::assertFalse($pattern->containsMinor());
        static::assertTrue($pattern->containsPatch());
        static::assertFalse($pattern->containsPreRelease());
        static::assertFalse($pattern->containsBuildMetadata());
    }

    public function testContainsMain()
    {
        $pattern = new Pattern('foo{MAIN}bar');

        static::assertTrue($pattern->containsMain());
        static::assertFalse($pattern->containsMajor());
        static::assertFalse($pattern->containsMinor());
        static::assertFalse($pattern->containsPatch());
        static::assertFalse($pattern->containsPreRelease());
        static::assertFalse($pattern->containsBuildMetadata());
    }

    public function testContainsBuildMetadata()
    {
        $pattern = new Pattern('foo{BUILDMETADATA}bar');

        static::assertFalse($pattern->containsMain());
        static::assertFalse($pattern->containsMajor());
        static::assertFalse($pattern->containsMinor());
        static::assertFalse($pattern->containsPatch());
        static::assertFalse($pattern->containsPreRelease());
        static::assertTrue($pattern->containsBuildMetadata());
    }

    public function testContains()
    {
        $pattern = new Pattern('foo{MAJOR}.{MINOR}bar');

        static::assertFalse($pattern->contains('main'));
        static::assertTrue($pattern->contains('major'));
        static::assertTrue($pattern->contains('minor'));
        static::assertFalse($pattern->contains('patch'));
        static::assertFalse($pattern->contains('prerelease'));
        static::assertFalse($pattern->contains('buildmetadata'));
    }

    public function testContainsMinor()
    {
        $pattern = new Pattern('foo{MINOR}bar');

        static::assertFalse($pattern->containsMain());
        static::assertFalse($pattern->containsMajor());
        static::assertTrue($pattern->containsMinor());
        static::assertFalse($pattern->containsPatch());
        static::assertFalse($pattern->containsPreRelease());
        static::assertFalse($pattern->containsBuildMetadata());
    }

    public function testMatch()
    {
        $pattern = new Pattern('foo/{MAJOR}.{MINOR}');

        static::assertTrue($pattern->match('foo/1.4'));
        static::assertFalse($pattern->match('foo/01.4'));
        static::assertFalse($pattern->match('1.4'));
    }

    public function testExtract()
    {
        $pattern = new Pattern('foo/{MAJOR}.{MINOR}');

        static::assertNull($pattern->extract('foo/01.4'));
        static::assertSame(['major' => 1, 'minor' => 4], $pattern->extract('foo/1.4'));
    }

    public function test__construct()
    {
        $pattern = new Pattern('foo_bar');

        static::assertSame('foo_bar', $pattern->getPattern());
        static::assertSame('/foo_bar/', $pattern->getExpression());

        $pattern = new Pattern('foo_{MAJOR}_bar');

        static::assertSame('foo_{MAJOR}_bar', $pattern->getPattern());
        static::assertSame('/foo_(?P<major>0|[1-9]\d*)_bar/', $pattern->getExpression());
    }

    public function testContainsPreRelease()
    {
        $pattern = new Pattern('foo{PRERELEASE}bar');

        static::assertFalse($pattern->containsMain());
        static::assertFalse($pattern->containsMajor());
        static::assertFalse($pattern->containsMinor());
        static::assertFalse($pattern->containsPatch());
        static::assertTrue($pattern->containsPreRelease());
        static::assertFalse($pattern->containsBuildMetadata());
    }
}
