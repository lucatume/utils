<?php
/**
 * Path utility functions.
 *
 * @package lucatume\Utils;
 */

namespace lucatume\Utils;

/**
 * Class Path
 *
 * @package lucatume\Utils
 */
class Path
{
    /**
     * Normalizes a path to the Unix standard.
     *
     * @param string $path The path to normalize.
     *
     * @return string The normalized path.
     */
    public static function normalize($path)
    {
        return implode('/', preg_split('#([/\\\])#u', $path) ?: []);
    }

    /**
     * Joins path fragments to form a unique, normalized, Unix path.
     *
     * @param mixed ...$frags The path fragments to join.
     *
     * @return string The joined, and Unix normalized, path fragments.
     */
    public static function join(...$frags)
    {
        return str_replace('\\', '/', implode(
            '/',
            array_reduce(
                $frags,
                static function (array $frags, $frag) {
                    static $count;

                    if ($count++ > 0) {
                        $frags[] = static::normalize(trim($frag, '\\/'));
                    } else {
                        $frags[] = static::normalize(rtrim($frag, '\\/'));
                    }

                    return $frags;
                },
                []
            )
        ));
    }

    /**
     * Returns the dir/file end of path.
     *
     * @param string $path The path to truncate.
     * @param int $length The length of the tail.
     *
     * @return string The last two components of a path.
     */
    public static function tail($path, $length = 2)
    {
        return implode('/', array_reverse(array_filter(
            array_map(static function () use (&$path) {
                $basename = basename($path);
                $path = dirname($path);
                return $basename;
            }, range(1, $length ?: 2))
        ))) ?: $path;
    }

    /**
     * Finds a parent directory that passes a check.
     *
     * @param string $dir The path to the directory to check.
     * @param callable $check The check to run on the directory.
     *
     * @return bool|string The directory path, or `false` if not found.
     */
    public static function findParentThat($dir, callable $check)
    {
        do {
            if ($check($dir)) {
                return $dir;
            }

            $parent = dirname($dir);

            if ($dir === $parent) {
                return false;
            }

            $dir = $parent;
        } while ($dir);

        return false;
    }

    /**
     * Finds a directory, child to the current one, that passes a check.
     *
     * @param string $dir The path to the directory to check.
     * @param callable $check The check to run on the directory.
     *
     * @return bool|string The directory path, or `false` if not found.
     */
    public static function findChildThat($dir, callable $check)
    {
        $found = $check($dir);

        if ($found) {
            return $dir;
        }

        $flags = \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::UNIX_PATHS
            | \FilesystemIterator::CURRENT_AS_PATHNAME;
        $dirs = new \CallbackFilterIterator(new \FilesystemIterator($dir, $flags), static function ($f) {
            return is_dir($f);
        });

        foreach ($dirs as $childDir) {
            if ($found = static::findChildThat($childDir, $check)) {
                return $found;
            }
        }

        return false;
    }

    /**
     * Removes trailing slash from a path.
     *
     * @param string $path The path to remove the trailing slash from.
     *
     * @return string The clean path.
     */
    public static function untrailslashit($path)
    {
        return rtrim($path, '/\\');
    }

    /**
     * Returns the resolved and normalized path for a file.
     *
     * @param string $path The path to resolve.
     * @param string|null $root The root dir to resolve the path from.
     *
     * @return string|false The resolved path, or `false` on failure.
     */
    public static function resolve($path, $root = null)
    {
        if (empty($path)) {
            return false;
        }

        if (strpos($path, '~') !== false) {
            try {
                $path = str_replace('~', static::home(), $path);
            } catch (\RuntimeException $e) {
                return false;
            }
        }

        if (false !== (realpath($path) === $path) && file_exists($path)) {
            return static::untrailslashit(static::normalize($path));
        }

        $realpath = realpath(static::join($root, $path));

        if (false === $realpath) {
            return false;
        }

        return $root ? static::untrailslashit(static::normalize($realpath)) : false;
    }

    /**
     * Returns the absolute path to the current user HOME directory.
     *
     * @param null|string $path An optional path to append to the current user HOME path.
     * @return string The absolute path to the current user HOME directory.
     *
     * @throws \RuntimeException If the current user HOME directory PATH cannot be resolved.
     */
    public static function home($path = null)
    {
        $home = isset($_SERVER['HOME']) ? $_SERVER['HOME'] : null;

        if ($home === null) {
            $homeDrive = isset($_SERVER['HOMEDRIVE']) ? $_SERVER['HOMEDRIVE'] : null;
            $homePath = isset($_SERVER['HOMEPATH']) ? $_SERVER['HOMEPATH'] : null;

            if ($homeDrive === null || $homePath === null) {
                throw new \RuntimeException('Cannot resolve HOME directory path.');
            }

            $home = static::join($homeDrive, $homePath);
        }

        if ($home === null) {
            throw new \RuntimeException('Cannot resolve HOME directory path.');
        }

        $home = rtrim($home, DIRECTORY_SEPARATOR);

        return $path === null ? $home : static::join($home, $path);
    }
}
