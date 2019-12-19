<?php
/**
 * String utility functions.
 *
 * @package tad\functions;
 */

namespace tad\functions;

/**
 * Create the slug version of a string.
 *
 * This will also convert `camelCase` to `camel-case`.
 *
 * @param string $string The string to create a slug for.
 * @param string $sep    The separator character to use, defaults to `-`.
 * @param bool   $let    Whether to let other common separators be or not.
 *
 * @return string The slug version of the string.
 */
function slug($string, $sep = '-', $let = false)
{
    $unquotedSeps = $let ? [ '-', '_', $sep ] : [ $sep ];
    $seps         = implode('', array_map(static function ($s) {
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
 * @since TBD
 *
 * @param string              $template The string template to render.
 * @param array<string,mixed> $data     The data that should be used to render the template, elements can also be
 *                                      `callable` that will be passed any argument defined by the `$fnArgs` parameter.
 * @param mixed[]             $fnArgs   An optional array of arguments that will be passed to each `$data` element that
 *                                      is a callable.
 *
 * @return string The string template, rendered using te provided data.
 *
 * @throws \InvalidArgumentException If the template fails to compile.
 */
function renderString($template, array $data = [], array $fnArgs = [])
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
