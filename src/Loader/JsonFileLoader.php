<?php
/*
 * This file is part of the Version package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Version\Loader;

use function file_get_contents;
use function is_string;
use function json_decode;
use const JSON_ERROR_CTRL_CHAR;
use const JSON_ERROR_DEPTH;
use const JSON_ERROR_STATE_MISMATCH;
use const JSON_ERROR_SYNTAX;
use const JSON_ERROR_UTF8;
use function json_last_error;
use function pathinfo;
use const PATHINFO_EXTENSION;
use SoureCode\Version\Exception\InvalidResourceException;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * Class JsonFileLoader.
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class JsonFileLoader extends FileLoader
{
    public function load($resource, string $type = null)
    {
        if ($data = file_get_contents($resource)) {
            $contents = json_decode($data, true);

            if (0 < $errorCode = json_last_error()) {
                throw new InvalidResourceException('Error parsing JSON: '.static::getJSONErrorMessage($errorCode));
            }

            return $contents;
        }

        return null;
    }

    /**
     * Translates JSON_ERROR_* constant into meaningful message.
     */
    private static function getJSONErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }

    public function supports($resource, string $type = null)
    {
        return is_string($resource) && ('json' === pathinfo($resource, PATHINFO_EXTENSION));
    }
}
