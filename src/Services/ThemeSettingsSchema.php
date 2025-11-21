<?php

namespace Luma\Core\Services;

class ThemeSettingsSchema
{
    private static bool $cache_set = false;
    private static array $cache = [];
    private static string $prefix = 'luma-core';

    public static function set_prefix($prefix): void {
        self::$prefix = $prefix;
    }

    public static function get_settings_list($prefix = false): array
    {
        // prefixed with sagewood if true
        $schema =  self::get();
        $list = [];
        $self_prefix = self::$prefix;
        foreach ($schema as $group => $values) {
            foreach ($values['settings'] as $id => $items) {
                if ($items['type'] !== 'subheading' || $items['type'] !== 'button') {
                    if ($prefix) {
                        $list["{$self_prefix}{$group}_{$id}"] = $items['label'];
                    } else {
                        $list["{$group}_{$id}"] = $items['label'];
                    }
                }
            }
        }
        return $list;
    }
    private static function set_cache(): void
    {
        self::$cache_set = true;
        self::$cache = [
            'header' => [
                'title' => 'Header',
                'priority' => 30,
                'settings' => [
                    'navbar_heading' => [
                        'label'     => 'Heading',
                        'type'      => 'subheading',
                        'priority'  => 5,
                    ],
                    'navbar_sticky' => [
                        'default'   => false,
                        'label'     => 'Enable sticky navbar',
                        'type'      => 'checkbox',
                        'priority'  => 6,
                    ],
                    'navbar_shrink' => [
                        'default'   => false,
                        'label'     => 'Shrink sticky navbar on scroll',
                        'type'      => 'checkbox',
                        'priority'  => 10,
                    ],
                    'navbar_transparent' => [
                        'default'   => false,
                        'label'     => 'Enable transparent navbar',
                        'type'      => 'checkbox',
                        'priority'  => 20,
                    ],
                    'navbar_full_width' => [
                        'default'   => false,
                        'label'     => 'Enable full width navbar',
                        'type'      => 'checkbox',
                        'priority'  => 25,
                    ],
                    'custom_header_heading' => [
                        'default'   => false,
                        'label'     => 'Enable Custom Image Header Heading',
                        'type'      => 'checkbox',
                        'priority'  => 30,
                    ],
                ],
            ],
            'display' => [
                'title' => 'Post and Page Display',
                'priority' => 35,
                'settings' => [
                    'post_width' => [
                        'default'   => 'default',
                        'label'     => 'Display width for posts:',
                        'type'      => 'radio',
                        'priority'  => 5,
                        'choices'   =>  [
                            'default' => 'Default',
                            'wide'    => 'Wide',
                        ],
                    ],
                    'page_width' => [
                        'default'   => 'wide',
                        'label'     => 'Display width for pages:',
                        'type'      => 'radio',
                        'priority'  => 10,
                        'choices'   =>  [
                            'default' => 'Default',
                            'wide'    => 'Wide',
                        ],
                    ],
                    // full display requires list format, conditinally controlled in customizer by js
                    // enforced by css due to .is-excerpt or .is-full classes on .archive-grid
                    'archive_view' => [ // was post_archive_display
                        'default'   => 'excerpt',
                        'label'     => 'On Archive Pages, posts show:',
                        'description' => 'Full requires list view below',
                        'type'      => 'radio',
                        'priority'  => 15,
                        'choices'   =>  [
                            'excerpt' => 'Summary',
                            'full'    => 'Full text',
                        ],
                        'partial' =>   [
                            'selector'        => '.archive-loop',
                            'render_callback' => function () {
                                get_template_part('src/views/content/content-archive');
                            },
                        ]
                    ],
                    'archive_excerpt_format' => [
                        'default'   => 'list',
                        'label'     => 'On Archive Pages, display posts excerpts in:',
                        'description' => 'Full requires list view below',
                        'type'      => 'radio',
                        'priority'  => 20,
                        'choices'   =>  [
                            'list' => 'List',
                            'grid'    => 'Grid',
                            'masonry'    => 'Masonry',
                        ],
                    ],
                    'post_author_bio' => [  // was 'post_display_author_bio'
                        'default'   => false,
                        'label'     =>  'On single post pages, show author bio in the footer',
                        'description' => '(if set up)',
                        'type'      => 'checkbox',
                        'priority'  => 25,
                        'partial' => [
                            'selector'        => '.author-bio',
                            'container_inclusive' => true,
                            'render_callback' => function () {
                                get_template_part('src/views/post/author-bio');
                            },
                        ]
                    ],
                ],
            ],
        ];
    }

    public static function get(): array
    {
        if (!self::$cache_set) {
            self::set_cache();
        }
        return self::$cache;
    }

    public static function theme_mod_with_default(string $full_key): mixed
    {
        if (!self::$cache_set) {
            self::set_cache();
        }

        // Extract group and key
        $parts = explode('_', $full_key, 2); // split into 2 parts only
        if (count($parts) < 2) {
            // fallback if key format is invalid
            return get_theme_mod($full_key);
        }

        [$group_name, $sub_key] = $parts;

        $self_prefix = self::$prefix;
        $prefixed_key = "{$self_prefix}_{$full_key}";

        // utlize default from settings list, if it exists
        if (isset(self::$cache[$group_name]['settings'][$sub_key])) {
            $default = self::$cache[$group_name]['settings'][$sub_key]['default'] ?? null;
            return get_theme_mod($prefixed_key, $default);
        }

        return get_theme_mod($prefixed_key);
    }
}
