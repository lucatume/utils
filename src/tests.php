<?php
/**
 * Test utility functions.
 *
 * @package tad\functions;
 */

namespace tad\functions;

/**
 * Returns the path to a file or directory in relation the current working directory or the `TEST_ROOT_DIR` env var.
 *
 * @param string $path The relative path to the data directory or file to fetch.
 *
 * @return string The absolute path to the data directory or file.
 */
function data($path = '/')
{
    $testRootDir = getenv('TEST_ROOT_DIR') ?: getcwd();

    if (false === $testRootDir) {
        throw new \RuntimeException(
            'Cannot find the "TEST_ROOT_DIR" env var or the current working directory.'
        );
    }

    $resolved = pathResolve(pathNormalize(pathJoin($testRootDir, '_data', $path)));

    if ($resolved === false) {
        throw new \RuntimeException('File "'.$path.'" not found, is the "TEST_ROOT_DIR" env var defined?');
    }

    return $resolved;
}

/**
 * Returns whether the current script was launched with a `--debug` flag or not.
 *
 * @return bool Whether the current script was launched with a `--debug` flag or not.
 *
 */
function isDebug()
{
    static $isDebug;

    if ($isDebug === null) {
        global $argc, $argv;
        $isDebug = $argc && count(array_filter($argv, static function ($arg) {
                return $arg === '--debug';
        }));
    }

    return $isDebug;
}
