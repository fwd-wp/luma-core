(function (wp) {
    if (typeof wp === 'undefined' || !wp.customize) return;
    const prefix = wpData.prefix;
    document.addEventListener('click', function (event) {
        const button = event.target.closest(buttonSelector);
        if (!button) return;

        const sectionId = button.dataset.section; // optional
        const idSuffix = button.dataset.subgroup;

        // find the section dynamically
        wp.customize.section(sectionId, function (section) {
            section.controls().forEach(control => {
                const setting = control.setting;
                const defaultValue = setting?.params?.default;

                if (setting && defaultValue !== undefined && setting.id.endsWith(idSuffix)) {
                    setting.set(defaultValue);
                    console.log(`Reset setting: ${setting.id} → ${defaultValue}`);
                }
            });
        });
    });

    // Shrink depends on Sticky
    wp.customize(`${prefix}_header_navbar_shrink`, (shrinkSetting) => {
        shrinkSetting.bind((value) => { // watches the setting
            if (value === true) {
                wp.customize(`${prefix}_header_navbar_sticky`, (stickySetting) => {
                    if (stickySetting.get() == false) { // use .get() as no need to watch
                        stickySetting.set(true); // auto-check sticky if shrink is on                    
                    }
                });
            }
        });
    });

    // Full archive view depends on list view (not grid or masonry)
    wp.customize(`${prefix}_display_archive_view`, (displaySetting) => {
        displaySetting.bind((value) => {
            if (value === 'full') {
                wp.customize(`${prefix}_display_archive_excerpt_format`, (formatSetting) => {
                    if (formatSetting.get() !== 'list') {
                        formatSetting.set('list');
                    }
                });
            }
        });
    });

    // Change full to excerpt if grid or masonry are chosen
    wp.customize(`${prefix}_display_archive_excerpt_format`, (formatSetting) => {
        formatSetting.bind((value) => {
            if (value !== 'list') {
                wp.customize(`${prefix}_display_archive_view`, (displaySetting) => {
                    if (displaySetting.get() !== 'excerpt') {
                        displaySetting.set('excerpt');
                    }
                });
            }
        });
    });

    /* reset font subgroups */
    bindSubgroupReset(
        `${prefix}_font_section`,
        '.customize-control-luma-core-customize-font-reset-button',
        '_body'
    );
})(wp);
