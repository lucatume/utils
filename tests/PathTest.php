<?php

namespace lucatume\Utils;

use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{

    public function pathJoinDataSet()
    {
        return [
            'empty'            => [ '', '' ],
            'one_component'    => [ '/foo-bar', '/foo-bar' ],
            'two_components'   => [ '/foo-bar/baz', '/foo-bar/', '/baz' ],
            'three_components' => [ 'C:/foo-bar/baz/test', 'C:\\foo-bar\\', '/baz', 'test' ],
        ];
    }

    /**
     * @dataProvider pathJoinDataSet
     */
    function test_pathJoin($expected, ...$input)
    {
        $this->assertEquals($expected, Path::join(...$input));
    }

    public function normalizePathDataSet()
    {
        return [
            'empty'         => [ '', '' ],
            'unix_abs_path' => [ '/foo/bar/baz', '/foo/bar/baz' ],
            'unix_rel_path' => [ 'foo/bar/baz', 'foo/bar/baz' ],
            'win_abs_path'  => [ '\foo\bar\baz', '/foo/bar/baz' ],
            'win_rel_path'  => [ 'foo\bar\baz', 'foo/bar/baz' ],
        ];
    }

    /**
     * @dataProvider normalizePathDataSet
     */
    function test_normalizePath($input, $expected)
    {
        $this->assertEquals($expected, Path::normalize($input));
    }

    public function pathTailDataSet()
    {
        return [
            'empty'       => [ '', '' ],
            'root'        => [ '/', '/' ],
            'len_2_req_0' => [ '/foo/bar', 'foo/bar', 0 ],
            'len_2_req_1' => [ '/foo/bar', 'bar', 1 ],
            'len_2_req_2' => [ '/foo/bar', 'foo/bar', 2 ],
            'len_2_req_3' => [ '/foo/bar', 'foo/bar', 3 ],
        ];
    }

    /**
     * @dataProvider pathTailDataSet
     */
    public function test_pathTail($input, $expected, $length = null)
    {
        $this->assertEquals($expected, Path::tail($input, $length));
    }

    public function renderStringDataProvider()
    {
        $session = static function ($session) {
            return 'xyz_' . $session;
        };

        return [
            'empty'                    => [ '', [], [], '' ],
            'empty_w_data'             => [ '', [ 'name' => 'luca' ], [], '' ],
            'empty_w_data_and_seed'    => [ '', [ 'name' => 'luca' ], [ 'session' => 'test' ], '' ],
            'template_w_data_and_seed' => [
                '{{session}}_{{name}}',
                [ 'name' => 'luca', 'session' => $session ],
                [ 'session' => 'test' ],
                'xyz_test_luca'
            ],
            'handlebar_template'       => [
                'render{{#if name}} with {{name}}{{/if}}',
                [ 'name' => 'luca' ],
                [],
                'render with luca'
            ]
        ];
    }

    public function findParentDirectoryThatDataSet()
    {
        yield 'same' => [
            Tests::data('folder-structures/wp-struct-1/wp'),
            Tests::data('folder-structures/wp-struct-1/wp')
        ];

        yield 'immediate_parent' => [
            Tests::data('folder-structures/wp-struct-1/wp/wp-content'),
            Tests::data('folder-structures/wp-struct-1/wp')
        ];

        yield 'removed_parent' => [
            Tests::data('folder-structures/wp-struct-1/wp/wp-content/plugins/test-plugin'),
            Tests::data('folder-structures/wp-struct-1/wp')
        ];

        yield 'not_available' => [
            __DIR__,
            false
        ];
    }

    /**
     * @dataProvider findParentDirectoryThatDataSet
     */
    public function test_findParentDirThat($input, $expected)
    {
        $check = static function ($dir) {
            return file_exists($dir . '/wp-load.php');
        };
        $this->assertEquals($expected, Path::findParentThat($input, $check));
    }

    public function findChildDirectoryThatDataSet()
    {
        yield 'same' => [
            Tests::data('folder-structures/wp-struct-1/wp'),
            Tests::data('folder-structures/wp-struct-1/wp')
        ];

        yield 'immediate_child' => [
            Tests::data('folder-structures/wp-struct-1'),
            Tests::data('folder-structures/wp-struct-1/wp')
        ];

        yield 'removed_child' => [
            Tests::data('folder-structures'),
            Tests::data('folder-structures/wp-struct-3')
        ];

        yield 'not_available' => [
            Tests::data('folder-structures/empty'),
            false
        ];
    }

    /**
     * @dataProvider findChildDirectoryThatDataSet
     * @param $input
     */
    public function test_findChildDirThat($input, $expected)
    {
        $check = static function ($dir) {
            return file_exists($dir . '/wp-load.php');
        };
        $this->assertEquals($expected, Path::findChildThat($input, $check));
    }
}
