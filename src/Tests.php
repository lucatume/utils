<?php
/**
 * Test utility functions.
 *
 * @package lucatume\Utils;
 */

namespace lucatume\Utils;

/**
 * Class Tests
 *
 * @package lucatume\Utils
 */
class Tests
{
    /**
     * Returns the path to a file or directory in relation the current working directory or the `TEST_ROOT_DIR` env var.
     *
     * @param string $path The relative path to the data directory or file to fetch.
     *
     * @return string The absolute path to the data directory or file.
     *
     * @throws \RuntimeException If the `TEST_ROOT_DIR` directory is not defined, does not exist, or the `tests`
     *                           directory does not exist.
     */
    public static function data($path = '/')
    {
        if (!$testRootDir = getenv('TEST_ROOT_DIR')) {
            $testRootDir = getcwd() ? Path::join(getcwd(), 'tests') : false;
        }

        if (false === $testRootDir) {
            throw new \RuntimeException(
                Strings::colorize(
                    'Cannot find the "TEST_ROOT_DIR" env var or the "/tests" directory.',
                    'light_red'
                )
            );
        }

        $resolved = Path::resolve(Path::normalize(Path::join($testRootDir, '_data', $path)));

        if ($resolved === false) {
            $str = 'File "' . $path . '" not found, is the "TEST_ROOT_DIR" env var defined?';
            throw new \RuntimeException(Strings::colorize($str, 'light_red'));
        }

        return $resolved;
    }

    /**
     * Returns the path to a file or directory in relation the cwd or the `TEST_VENDOR_DIR` env var.
     *
     * @param string $path The relative path to the data directory or file to fetch.
     *
     * @return string The absolute path to the data directory or file.
     *
     * @throws \RuntimeException If the `TEST_VENDOR_DIR` directory is not defined, does not exist, or the `tests`
     *                           directory  does not exist.
     */
    public static function vendor($path = '/')
    {
        if (!$vendorDir = getenv('VENDOR_ROOT_DIR')) {
            $vendorDir = getcwd() ? Path::join(getcwd(), 'vendor') : false;
        }

        if (false === $vendorDir) {
            throw new \RuntimeException(
                Strings::colorize(
                    'Cannot find the "VENDOR_ROOT_DIR" env var or "/vendor" directory.',
                    'light_red'
                )
            );
        }

        $resolved = Path::resolve(Path::normalize(Path::join($vendorDir, $path)));

        if ($resolved === false) {
            throw new \RuntimeException(
                Strings::colorize(
                    'File "' . $path . '" not found, is the "VENDOR_ROOT_DIR" env var defined?',
                    'light_red'
                )
            );
        }

        return $resolved;
    }

    /**
     * Returns whether the current script was launched with a `--debug` flag or not.
     *
     * @return bool Whether the current script was launched with a `--debug` flag or not.
     *
     */
    public static function isDebug()
    {
        static $isDebug;

        if ($isDebug === null) {
            global $argc, $argv;
            $isDebug = $argc && count(array_filter($argv, static function ($arg) {
                    return in_array($arg, ['--debug', '--verbose', '-vvv']);
            }));
        }

        return $isDebug;
    }
}
