<?php
/**
 * Test utility functions.
 *
 * @package lucatume\functions;
 */

namespace lucatume\functions;

/**
 * Returns the path to a file or directory in relation the current working directory or the `TEST_ROOT_DIR` env var.
 *
 * @param string $path The relative path to the data directory or file to fetch.
 *
 * @return string The absolute path to the data directory or file.
 *
 * @throws \RuntimeException If the `TEST_ROOT_DIR` directory is not defined, does not exist, or the `tests` directory
 *                           does not exist.
 */
function data($path = '/')
{
    if (!$testRootDir = getenv('TEST_ROOT_DIR')) {
        $testRootDir = getcwd() ? pathJoin(getcwd(), 'tests') : false;
    }

    if (false === $testRootDir) {
        throw new \RuntimeException(
            colorize(
                'Cannot find the "TEST_ROOT_DIR" env var or the "/tests" directory.',
                'light_red'
            )
        );
    }

    $resolved = pathResolve(pathNormalize(pathJoin($testRootDir, '_data', $path)));

    if ($resolved === false) {
        $str = 'File "' . $path . '" not found, is the "TEST_ROOT_DIR" env var defined?';
        throw new \RuntimeException(colorize($str, 'light_red'));
    }

    return $resolved;
}

/**
 * Returns the path to a file or directory in relation the current working directory or the `TEST_VENDOR_DIR` env var.
 *
 * @param string $path The relative path to the data directory or file to fetch.
 *
 * @return string The absolute path to the data directory or file.
 *
 * @throws \RuntimeException If the `TEST_VENDOR_DIR` directory is not defined, does not exist, or the `tests` directory
 *                           does not exist.
 */
function vendor($path = '/')
{
    if (!$vendorDir = getenv('VENDOR_ROOT_DIR')) {
        $vendorDir = getcwd() ? pathJoin(getcwd(), 'vendor') : false;
    }

    if (false === $vendorDir) {
        throw new \RuntimeException(
            colorize(
                'Cannot find the "VENDOR_ROOT_DIR" env var or "/vendor" directory.',
                'light_red'
            )
        );
    }

    $resolved = pathResolve(pathNormalize(pathJoin($vendorDir, $path)));

    if ($resolved === false) {
        throw new \RuntimeException(
            colorize(
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
function isDebug()
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

/**
 * Outputs a debug message if debug is active.
 *
 * @param string|mixed $message A debug message, object or array to print.
 * @param string|null $section The section this message belongs to.
 */
function debug($message, string $section = null)
{
    if (!isDebug()) {
        return;
    }

    $formattedMessage = $message;

    if (is_object($message)) {
        if (method_exists($message, '__toString')) {
            $formattedMessage = $message->__toString();
        } else {
            $formattedMessage = json_encode($message);
        }
    } else if (is_array($message)) {
        $formattedMessage = json_encode($message);
    }

    if ($section !== null) {
        $formattedMessage = '[' . $section . '] ' . $formattedMessage;
    }

    fwrite(STDOUT, $formattedMessage . "\n");
//    echo "\t" . colorize($formattedMessage, 'cyan');
}
