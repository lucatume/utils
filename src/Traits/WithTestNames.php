<?php
/**
 * Methods to "look around" inside test cases and fetch meta test information.
 *
 * @since   TBD
 *
 * @package lucatume\TestUtils
 */

namespace lucatume\Utils\Traits;

use PHPUnit\Framework\TestCase;
use function lucatume\functions\slug;

trait WithTestNames
{

    /**
     * Find the caller test method name from the debug back-trace.
     *
     * @return string The caller method name.
     *
     * @throws \RuntimeException If the test method name cannot be found.
     */
    protected function getTestMethodName()
    {
        $trace = debug_backtrace();

        foreach ($trace as $entry) {
            if (isset($entry['object'], $entry['function']) && $entry['object'] instanceof TestCase) {
                return slug($entry['class'], '_') .'__'. $entry['function'];
            }
        }

        throw new \RuntimeException('Cannot find the test method name; ' .
                                     'was this method called from a PHPUnit test case?');
    }
}
