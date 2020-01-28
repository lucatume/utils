<?php
/**
 * String utility functions.
 *
 * @package lucatume\Utils;
 */

namespace lucatume\Utils;

/**
 * Class String
 *
 * @package lucatume\Utils
 */
class Strings
{
    /**
     * Create the slug version of a string.
     *
     * This will also convert `camelCase` to `camel-case`.
     *
     * @param string $string The string to create a slug for.
     * @param string $sep The separator character to use, defaults to `-`.
     * @param bool $let Whether to let other common separators be or not.
     *
     * @return string The slug version of the string.
     */
    public static function slug($string, $sep = '-', $let = false)
    {
        $unquotedSeps = $let ? ['-', '_', $sep] : [$sep];
        $seps = implode('', array_map(static function ($s) {
            return preg_quote($s, '~');
        }, array_unique($unquotedSeps)));

        $steps = [
            // Prepend the separator to the first uppercase letter and trim the string.
            static function ($s) use ($seps, $sep) {
                return preg_replace('/(?<![A-Z' . $seps . '])([A-Z])/u', $sep . '$1', trim($s));
            },
            // Replace non letter or digits with the separator.
            static function ($s) use ($seps, $sep) {
                return preg_replace('~[^\pL\d' . $seps . ']+~u', $sep, $s);
            },
            // Transliterate.
            static function ($s) {
                return iconv('utf-8', 'us-ascii//TRANSLIT', $s);
            },
            // Remove anything that is not a word or a number or the separator(s).
            static function ($s) use ($seps) {
                return preg_replace('~[^' . $seps . '\w]+~', '', $s);
            },
            // Trim excess separator chars.
            static function ($s) use ($seps) {
                return trim(trim($s), $seps);
            },
            // Remove duplicate separators and lowercase.
            static function ($s) use ($seps, $sep) {
                return preg_replace('~[' . $seps . ']{2,}~', $sep, $s);
            },
            // Empty strings are fine here.
            'strtolower'
        ];

        $stepResult = $string;
        foreach ($steps as $step) {
            if (empty($step($stepResult))) {
                break;
            }
            $stepResult = $step($stepResult);
        }

        return empty($stepResult) ? $string : $stepResult;
    }

    /**
     * Renders a template string using Handlebars-compatible syntax.
     *
     * @param string $template The string template to render.
     * @param array<string,mixed> $data The data that should be used to render the template, elements can be
     *                                      `callable` that will be passed args defined by the `$fnArgs` parameter.
     * @param mixed[] $fnArgs An optional array of arguments that will be passed to each `$data` element that
     *                                      is a callable.
     *
     * @return string The string template, rendered using te provided data.
     *
     * @throws \InvalidArgumentException If the template fails to compile.
     * @since TBD
     *
     */
    public static function template($template, array $data = [], array $fnArgs = [])
    {
        $fnArgs = array_values($fnArgs);

        $replace = array_map(
            static function ($value) use ($fnArgs) {
                return is_callable($value) ? $value(...$fnArgs) : $value;
            },
            $data
        );

        if (false !== strpos($template, '{{#')) {
            $compiled = \LightnCandy\LightnCandy::compile($template);

            if ($compiled === false) {
                throw new \InvalidArgumentException('Failed to compile template: ' . $template);
            }

            $compiler = \LightnCandy\LightnCandy::prepare($compiled);

            if ($compiler === false) {
                throw new \RuntimeException('Failed to prepare template.');
            }

            /** @var \Closure $compiler */
            return $compiler($replace);
        }

        $search = array_map(
            static function ($k) {
                return '{{' . $k . '}}';
            },
            array_keys($data)
        );

        return str_replace($search, $replace, $template);
    }

    /**
     * Colorize a string.
     *
     * @param string $string The string to colorize.
     * @param string|null $foreground The foreground color code, e.g. `white` or `light_gray`.
     * @param string|null $background The background color code, e.g. `red` or `light_gray`.
     *
     * @return string The colorized string.
     */
    public static function colorize($string, $foreground = null, $background = null)
    {
        static $foregroundColors = [
            'black' => '0;30',
            'dark_gray' => '1;30',
            'blue' => '0;34',
            'light_blue' => '1;34',
            'green' => '0;32',
            'light_green' => '1;32',
            'cyan' => '0;36',
            'light_cyan' => '1;36',
            'red' => '0;31',
            'light_red' => '1;31',
            'purple' => '0;35',
            'light_purple' => '1;35',
            'brown' => '0;33',
            'yellow' => '1;33',
            'light_gray' => '0;37',
            'white' => '1;37'
        ];

        static $backgroundColors = [
            'black' => '40',
            'red' => '41',
            'green' => '42',
            'yellow' => '43',
            'blue' => '44',
            'magenta' => '45',
            'cyan' => '46',
            'light_gray' => '47'
        ];

        $colored_string = '';

        // Check if given foreground color found
        if (isset($foregroundColors[$foreground])) {
            $colored_string .= "\033[" . $foregroundColors[$foreground] . 'm';
        }
        // Check if given background color found
        if (isset($backgroundColors[$background])) {
            $colored_string .= "\033[" . $backgroundColors[$background] . 'm';
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }
}
