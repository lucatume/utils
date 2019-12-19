<?php
/**
 * Methods to "look around" inside test cases and fetch meta test information.
 *
 * @since   TBD
 *
 * @package tad\TestUtils
 */

namespace tad\Utils\Traits;

use PHPUnit\Framework\TestCase;

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
                return $entry['function'];
            }
        }

        throw new \RuntimeException('Cannot find the test method name; ' .
                                     'was this method called from a PHPUnit test case?');
    }
}
