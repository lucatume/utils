<?php

namespace tad\functions;

use PHPUnit\Framework\TestCase;

class stringTest extends TestCase
{
    /**
     * Test slugify
     * @dataProvider slugifyDataProvider
     */
    public function test_slugify($input, $expected, $sep = null, $let = null)
    {
        $this->assertEquals($expected, slug(...array_slice(func_get_args(), 1)));
    }

    public function slugifyDataProvider()
    {
        return [
            'empty_string'                 => [ '', '', '-', false ],
            'one_word'                     => [ 'test', 'test', '-', false ],
            'camelcase_str'                => [ 'testStringIsSlugified', 'test-string-is-slugified', '-', false ],
            'camelcase_str_w_numbers'      => [ 'testString2IsSlugified', 'test-string-2-is-slugified', '-', false ],
            'snake_case_str'               => [ 'test_string_is_slugified', 'test-string-is-slugified', '-', false ],
            'snake_case_str_w_number'      => [
                'test_string_2_is_slugified',
                'test-string-2-is-slugified',
                '-',
                false
            ],
            'words'                        => [ 'Lorem dolor sit', 'lorem-dolor-sit', '-', false ],
            'words_and_numbers'            => [
                'Lorem dolor sit 23 et lorem 89',
                'lorem-dolor-sit-23-et-lorem-89',
                '-',
                false
            ],
            '_empty_string'                => [ '', '', '_', false ],
            '_one_word'                    => [ 'test', 'test', '_', false ],
            '_camelcase_str'               => [ 'testStringIsSlugified', 'test_string_is_slugified', '_', false ],
            '_camelcase_str_w_numbers'     => [ 'testString2IsSlugified', 'test_string_2_is_slugified', '_', false ],
            '_snake_case_str'              => [ 'test_string_is_slugified', 'test_string_is_slugified', '_', false ],
            '_snake_case_str_w_number'     => [
                'test_string_2_is_slugified',
                'test_string_2_is_slugified',
                '_',
                false
            ],
            '_words'                       => [ 'Lorem dolor sit', 'lorem_dolor_sit', '_', false ],
            '_words_and_numbers'           => [
                'Lorem dolor sit 23 et lorem 89',
                'lorem_dolor_sit_23_et_lorem_89',
                '_',
                false
            ],
            'let_camelcase_str'            => [ 'testStringIsSlugified', 'test-string-is-slugified', '-', true ],
            'let_camelcase_str_w_numbers'  => [ 'testString2IsSlugified', 'test-string-2-is-slugified', '-', true ],
            'let_snake_case_str'           => [ 'test_string_is_slugified', 'test_string_is_slugified', '-', true ],
            'let_snake_case_str_2'         => [ 'test_string_Is_Slugified', 'test_string_is_slugified', '-', true ],
            'let_snake_case_str_3'         => [
                'test_string_23_is_slugified',
                'test_string_23_is_slugified',
                '-',
                true
            ],
            'let_snake_case_str_w_number'  => [ 'test_string_2_is_slugified', 'test_string_2_is_slugified', '-', true ],
            '_let_camelcase_str'           => [ 'testStringIsSlugified', 'test_string_is_slugified', '_', true ],
            '_let_camelcase_str_w_numbers' => [ 'testString2IsSlugified', 'test_string_2_is_slugified', '_', true ],
            '_let_snake_case_str'          => [ 'test_string_is_slugified', 'test_string_is_slugified', '_', true ],
            '_let_snake_case_str_w_number' => [ 'test_string_2_is_slugified', 'test_string_2_is_slugified', '_', true ],
            '_let_hyphen_string'           => [ 'test-string-is-slugified', 'test-string-is-slugified', '_', true ],
            '_let_hyphen_string_w_number'  => [ 'test-string-2-is-slugified', 'test-string-2-is-slugified', '_', true ],
            'cat1'                         => [ 'cat1', 'cat1' ]
        ];
    }

    /**
     * Test renderString
     * @dataProvider renderStringDataProvider
     */
    public function test_render_string($template, $data, $fnArgs, $expected)
    {
        $this->assertEquals($expected, renderString($template, $data, $fnArgs));
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
}
