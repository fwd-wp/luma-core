(function (wp) {
    if (typeof wp === 'undefined' || !wp.customize) return;

    // Helper: Update text content live
    function bindTextSetting(settingId, selector) {
        wp.customize(settingId, function (value) {
            value.bind(function (newValue) {
                const el = document.querySelector(selector);
                if (el) el.textContent = newValue || '';
            });
        });
    }

    // Helper: Update CSS variable live
    function bindCssVariable(settingId, variableName, prefix = '', suffix = '') {
        wp.customize(settingId, function (value) {
            value.bind(function (newValue) {
                if (newValue !== undefined) {
                    document.documentElement.style.setProperty(variableName, `${prefix}${newValue}${suffix}`);
                }
            });
        });
    }

    // Lookup helper
    function bindSettingWithLookup(settingId, variableName, fontArray) {
        wp.customize(settingId, function (value) {
            value.bind(function (slug) {
                const font = fontArray.find(f => f.slug === slug);
                if (font) {
                    document.documentElement.style.setProperty(variableName, `var(${font.css_var})`);
                }
            });
        });
    }

    // Helper: Toggle visibility live
    function bindVisibilitySetting(settingId, selector) {
        wp.customize(settingId, function (value) {
            value.bind(function (isVisible) {
                const el = document.querySelector(selector);
                if (el) {
                    el.style.display = isVisible ? '' : 'none';
                }
            });
        });
    }

    // Helper: Toggle a class if setting matches expectedValue
    function bindClassToggle(settingId, selector, className, expectedValue = true) {
        wp.customize(settingId, function (value) {
            value.bind(function (newValue) {
                const el = document.querySelector(selector);
                if (el) {
                    el.classList.toggle(className, newValue === expectedValue);
                }
            });
        });
    }

    // converts snake case to kebab
    function snakeToKebab(str) {
        return str.replace(/_/g, '-');
    }

    // ---- Site identity ----
    bindTextSetting('blogname', '.site-title a'); // other pages
    bindTextSetting('blogname', '.site-title'); // home page
    //bindTextSetting('blogdescription', '.site-description');
    bindVisibilitySetting('luma_core_display_title_and_tagline', '.site-branding-text');

    // ---- Header options ----
    bindClassToggle('luma_core_header_sticky', '.site-header-container', 'is-sticky');
    bindClassToggle('luma_core_header_transparent', '.site-header-container', 'is-transparent');
    bindClassToggle('luma_core_header_nav_full', '.site-header', 'is-full-width');
    // see end of file for luma_core_header_shrink
    bindTextSetting('blogname', '.site-title--custom-header'); // home page
    bindTextSetting('blogdescription', '.site-description--custom-header');
    bindVisibilitySetting('display_header_text', '.custom-header-image-inner');

    // ---- Display options ----
    bindClassToggle('luma_core_post_width', 'body', 'is-wide-single');
    bindClassToggle('luma_core_post_page_width', 'body', 'is-wide-page');
    // 'luma_core_post_archive_display' -> selective refresh partial
    // 'luma_core_post__archive_format' handled in archive-masonry.js (enqueued by customize, also conditionally on front end)
    // 'luma_core_post_display_author_bio' -> selective refresh partial


    // ---- Color palette  ----
    if (Array.isArray(colorPalette)) {
        colorPalette.forEach(function (item) {
            if (!item.slug || !item.color) return;
            bindCssVariable(
                `color_${item.slug}`,
                `${item.css_var}`
            );
        });
    }

    // ---- Typography
    const categories = ['body', 'heading', 'custom_header'];

    categories.forEach(category => {

        // Font Family
        if (category === 'body' || category === 'heading') {
            const settingId = `font_family_${category}`;
            const variableName = `--wp--custom--font--family--${category}`;
            const fontArray = window[`$fontFamilies`];

            if (fontArray) {
                bindSettingWithLookup(settingId, variableName, fontArray);
            }
        }

        // Font Weight - all categories
        bindCssVariable(
            `font_weight_${category}`,
            `--wp--custom--font--weight--${snakeToKebab(category)}`
        );

        // Line Height
        if (category === 'body' || category === 'heading') {
            bindCssVariable(
                `font_line_height_${category}`,
                `--wp--custom--font--line-height--${snakeToKebab(category)}`
            );
        }
        // Font Family
        if (category === 'body') {
            const settingId = `font_size_${category}`;
            const variableName = `--wp--custom--font--size--${snakeToKebab(category)}`;
            const fontArray = window[`fontSizes`];

            if (fontArray) {
                bindSettingWithLookup(settingId, variableName, fontArray);
            }
        }
    });



    // init navbar shrink script
    wp.customize('luma_core_header_shrink', function (value) {
        value.bind(function (enabled) {
            const header = document.querySelector('.site-header');

            if (!header) return;

            if (enabled) {
                header.classList.add('is-shrink-enabled');

                if (typeof window.initNavbarShrink === 'function') {
                    window.initNavbarShrink(); // call your shrink init from navbar-shrink.js
                }
            } else {
                header.classList.remove('is-shrink-enabled');

                if (typeof window.destroyNavbarShrink === 'function') {
                    window.destroyNavbarShrink(); // optional cleanup if your script supports it
                }
            }
        });
    });

})(wp);
