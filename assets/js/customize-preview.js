
(function (wp) {
    if (!wp || !wp.customize) return;
    wp.customize.bind('preview-ready', function () {
        const prefix = wpData.prefix;
        //console.log(wpData);

        /**
 * Reset a subgroup of settings in a section based on ID suffix.
 *
 * @param {string} sectionId - The Customizer section ID.
 * @param {string} buttonSelector - Selector for the reset button.
 * @param {string} idSuffix - The suffix used to identify subgroup settings (e.g., "_body").
 */


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

                    if (newValue === undefined || newValue === null) {
                        return;
                    }

                    const cssValue = String(newValue);

                    document.documentElement.style.setProperty(
                        variableName,
                        `${prefix}${cssValue}${suffix}`
                    );
                });
            });
        }

        // Lookup helper
        function bindSettingWithLookup(settingId, variableName, themeJsonArray) {
            wp.customize(settingId, function (value) {
                value.bind(function (slug) {
                    const style = themeJsonArray.find(s => s.slug === slug);
                    if (style) {
                        document.documentElement.style.setProperty(variableName, `var(${style.css_var})`);
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

        /**
     * Toggle a class on an element based on a Customizer setting.
     *
     * @param {string} settingId The Customizer setting ID.
     * @param {string} selector CSS selector for the element to toggle class on.
     * @param {string} className Class to toggle.
     * @param {string|boolean|number} expectedValue Value that triggers class toggle (default: true).
     */
        function bindClassToggle(settingId, selector, className, expectedValue = true) {
            wp.customize(settingId, function (value) {
                value.bind(function (newValue) {
                    const el = document.querySelector(selector);
                    if (!el) return;

                    // Normalize types: convert to string for select/text, keep boolean for checkboxes
                    let normalizedNewValue = newValue;
                    let normalizedExpected = expectedValue;

                    if (typeof newValue === 'string' || typeof expectedValue === 'string') {
                        normalizedNewValue = String(newValue).trim();
                        normalizedExpected = String(expectedValue).trim();
                    }

                    el.classList.toggle(className, normalizedNewValue === normalizedExpected);
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
        bindTextSetting('blogname', '.wp-custom-header-title'); // custom header
        bindTextSetting('blogdescription', '.wp-custom-header-description'); // custom header
        bindVisibilitySetting('display_title_and_tagline', '.wp-custom-header-inner');

        wp.customize('header_textcolor', function (value) {
            value.bind(function (newColor) {
                const elements = document.querySelectorAll('.wp-custom-header-title, .wp-custom-header-description');

                elements.forEach(el => {
                    if (newColor === 'blank') {
                        el.style.display = 'none';
                    } else {
                        el.style.display = '';
                        el.style.color = '#' + newColor;
                    }
                });
            });
        });

        // ---- Header  ----
        bindClassToggle(`${prefix}_header_navbar_sticky`, '.site-header-container', 'is-sticky', true);
        // _header_navbar_shrink handled below
        bindClassToggle(`${prefix}_header_navbar_transparent`, '.site-header-container', 'is-transparent', true);
        bindClassToggle(`${prefix}_header_navbar_full_width`, '.site-header', 'is-full', true);
        // _header_custom_header_enable_subheading TODO: add selective refresh partial when implemented
        // see end of file for    _header_navbar_shrink
        bindTextSetting('blogname', '.site-title--custom-header'); // home page
        bindTextSetting('blogdescription', '.site-description--custom-header');
        bindVisibilitySetting('display_header_text', '.custom-header-image-inner');

        // ---- Display ----
        bindClassToggle(`${prefix}_display_post_width`, 'body', 'is-wide', 'wide');
        bindClassToggle(`${prefix}_display_page_width`, 'body', 'is-wide', 'wide');
        // '_display_archive_view' -> selective refresh partial
        // '_display_archive_excerpt_format' handled below
        // '_display_archive_excerpt_length' -> selective refresh partial
        // '_display_post_author_bio' -> selective refresh partial


        // ---- Colors
        for (const value of Object.values(wpData.colorSettings)) {
            bindCssVariable(
                value.setting_id_prefixed,
                value.css_var
            );
        }

        // ---- Typography
        for (const value of Object.values(wpData.fontSettings)) {

            if (value.property === 'weight' || value.property === 'line-height') {
                bindCssVariable(
                    value.setting_id_prefixed ?? '',
                    value.css_var ?? ''
                );
                continue;
            }

            if (value.property === 'family' || value.property === 'size') {
                const themeJsonArray =
                    value.property === 'family'
                        ? wpData.fontFamilies
                        : wpData.fontSizes;

                bindSettingWithLookup(
                    value.setting_id_prefixed,
                    value.css_var,
                    themeJsonArray
                );
            }
        }




        // Init navbar shrink script
        wp.customize(`${prefix}_header_navbar_shrink`, function (value) {
            value.bind(function (newValue) {
                const header = document.querySelector('.site-header');
                if (!header) return;

                header.classList.toggle('is-shrink-enabled', !!newValue);

                if (newValue) {
                    if (typeof window.initNavbarShrink === 'function') {
                        window.initNavbarShrink();
                    }
                } else {
                    if (typeof window.destroyNavbarShrink === 'function') {
                        window.destroyNavbarShrink();
                    }
                }
            });
        });

        // Initialize masonry/grid classes and scripts
        wp.customize(`${prefix}_display_archive_excerpt_format`, function (value) {
            value.bind(function (newValue) {
                const body = document.querySelector('body');
                if (!body) return;

                body.classList.remove('is-masonry', 'is-grid');

                if (newValue === 'masonry') {
                    body.classList.add('is-masonry');
                    const grid = document.querySelector('.archive-grid');
                    if (grid) {
                        window.masonryInstance = window.initMasonry(grid);
                    }
                } else {
                    if (typeof window.destroyMasonry === 'function') {
                        window.destroyMasonry();
                    }
                    if (newValue === 'grid') body.classList.add('is-grid');
                }
            });
        });
    })
})(wp);