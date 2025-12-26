<?php

namespace Luma\Core\Customize;

class StaticCustomizeSettings
{
    public static function get(): array
    {
        $settings = [
            'wp-core' => [
                'settings' => [
                    'display_title_and_tagline' => [
                        'default'   => true,
                    ],
                ],
            ],
            'header' => [
                'title' => 'Header',
                'priority' => 30,
                'settings' => [
                    'navbar_subheading' => [
                        'label'     => 'Navbar',
                        'type'      => 'subheading',
                        'priority'  => 5,
                    ],
                    'navbar_display_title' => [
                        'default'   => true,
                        'label'     => 'Display Site Title in Navbar',
                        'type'      => 'checkbox',
                        'priority'  => 10,
                    ],
                    'navbar_sticky' => [
                        'default'   => false,
                        'label'     => 'Enable sticky navbar',
                        'type'      => 'checkbox',
                        'priority'  => 15,
                    ],
                    'navbar_shrink' => [
                        'default'   => false,
                        'label'     => 'Shrink sticky navbar on scroll',
                        'type'      => 'checkbox',
                        'priority'  => 20,
                    ],
                    'navbar_transparent' => [
                        'default'   => false,
                        'label'     => 'Enable transparent navbar',
                        'type'      => 'checkbox',
                        'priority'  => 25,
                    ],
                    'navbar_full_width' => [
                        'default'   => false,
                        'label'     => 'Enable full width navbar',
                        'type'      => 'checkbox',
                        'priority'  => 30,
                    ],
                    'custom_header_subheading' => [
                        'label'     => 'Custom Header Image',
                        'type'      => 'subheading',
                        'priority'  => 40,
                    ],
                    'custom_header_enabled' => [
                        'default'   => false,
                        'label'     => 'Enable Custom Image Header',
                        'description' => 'Displayed under navbar. Settings contained within header media section.',
                        'type'      => 'checkbox',
                        'priority'  => 45,
                        'partial' =>   [
                            'selector'        => '.custom-header',
                            'render_callback' => function () {
                                get_template_part('template-parts/header/site-custom-header-image.php');
                            },
                        ]
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
                                get_template_part('template-parts/content/content-archive');
                            },
                        ]
                    ],
                    'archive_excerpt_format' => [
                        'default'   => 'list',
                        'label'     => 'On Archive Pages, display posts excerpts in:',
                        'description' => 'Grid and Masonry require Summary view above.',
                        'type'      => 'radio',
                        'priority'  => 25,
                        'choices'   =>  [
                            'list' => 'List',
                            'grid'    => 'Grid',
                            'masonry'    => 'Masonry',
                        ],
                    ],
                    'archive_excerpt_length' => [
                        'default'   => 25,
                        'label'     => 'Post summary length',
                        'description' => 'On Archive Pages, with summary view, number of words displayed',
                        'type'      => 'number',
                        'priority'  => 20,
                        'input_attrs' => [
                            'min'  => 10,
                            'max'  => 50,
                            'step' => 1,
                        ],
                        'partial' =>   [
                            'selector'        => '.archive-loop',
                            'render_callback' => function () {
                                get_template_part('template-parts/content/content-archive');
                            },
                        ]
                    ],
                    'post_author_bio' => [
                        'default'   => true,
                        'label'     =>  'On single post pages, show author bio in the footer',
                        'description' => '(if set up)',
                        'type'      => 'checkbox',
                        'priority'  => 30,
                        'partial' => [
                            'selector'        => '.author-bio',
                            'container_inclusive' => true,
                            'render_callback' => function () {
                                get_template_part('template-parts/post/author-bio');
                            },
                        ]
                    ],
                ],
            ],
        ];
        return $settings;
    }

    /**
     * Get all font categories and their properties.
     *
     * @since Luma-Core 1.0
     *
     * @return array<string, array<string, mixed>> Array of font categories and their properties.
     */
    public static function get_font_categories(): array
    {
        $categories = [
            'body' => [
                'subheading'  => ['label' => 'Body',        'type' => 'subheading',],
                'family'      => ['label' => 'Font Family', 'type' => 'select', 'choices' => 'fontFamilies',],
                'weight'      => ['label' => 'Font Weight', 'type' => 'number', 'min' => 300, 'max' => 600,],
                'line-height' => ['label' => 'Line Height', 'type' => 'number', 'min' => 1.2, 'max' => 2.0,],
                'size'        => ['label' => 'Font Size',   'type' => 'select', 'choices' => 'fontSizes',],
                'reset'       => ['label' => 'Reset',       'type' => 'button',],
            ],
            'heading' => [
                'subheading'  => ['label'  => 'Heading',    'type' => 'subheading',],
                'family'      => ['label' => 'Font Family', 'type' => 'select', 'choices' => 'fontFamilies',],
                'weight'      => ['label' => 'Font Weight', 'type' => 'number', 'min' => 400, 'max' => 900,],
                'line-height' => ['label' => 'Line Height', 'type' => 'number', 'min' => 1.0, 'max' => 1.5,],
                'reset'       => ['label' => 'Reset',       'type' => 'button',],

            ],
        ];

        // Optionally add custom header if supported and enabled
        if (current_theme_supports('custom-header') && get_header_image()) {
            $categories['custom-header'] = [
                'subheading'  => ['label' => 'Image Header', 'type' => 'subheading'],
                'family'      => ['label' => 'Font Family', 'type' => 'select', 'choices' => 'fontFamilies',],
                'weight'      => ['label' => 'Font Weight', 'type' => 'number', 'min' => 400, 'max' => 700,],
                'line-height' => ['label' => 'Line Height', 'type' => 'number', 'min' => 1.0, 'max' => 1.5,],
            ];
        }

        return $categories;
    }
}
