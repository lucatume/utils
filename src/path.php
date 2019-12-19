<?php
/**
 * Path utility functions.
 *
 * @package tad\functions;
 */

namespace tad\functions;

/**
 * Normalizes a path to the Unix standard.
 *
 * @param string $path The path to normalize.
 *
 * @return string The normalized path.
 */
function pathNormalize($path)
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
function pathJoin(...$frags)
{
    return str_replace('\\', '/', implode(
        '/',
        array_reduce(
            $frags,
            static function (array $frags, $frag) {
                static $count;

                if ($count++ > 0) {
                    $frags[] = pathNormalize(trim($frag, '\\/'));
                } else {
                    $frags[] = pathNormalize(rtrim($frag, '\\/'));
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
function pathTail($path, $length = 2)
{
    return implode('/', array_reverse(array_filter(
        array_map(static function () use (&$path) {
            $basename = basename($path);
            $path = dirname($path);
            return $basename;
        }, range(1, $length?:2))
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
function findParentDirThat($dir, callable $check)
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
function findChildDirThat($dir, callable $check)
{
    $found = $check($dir);

    if ($found) {
        return $dir;
    }

    $dirs = new \CallbackFilterIterator(
        new \FilesystemIterator(
            $dir,
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::CURRENT_AS_PATHNAME
        ),
        static function ($f) {
            return is_dir($f);
        }
    );

    foreach ($dirs as $childDir) {
        if ($found = findChildDirThat($childDir, $check)) {
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
function pathUntrailslashit($path)
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
function pathResolve($path, $root = null)
{
    if (empty($path)) {
        return false;
    }

    if (false !== ( realpath($path) === $path ) && file_exists($path)) {
        return pathUntrailslashit(pathNormalize($path));
    }

    $realpath = realpath(pathJoin($root, $path));

    if (false === $realpath) {
        return false;
    }

    return $root ? pathUntrailslashit(pathNormalize($realpath)) : false;
}
