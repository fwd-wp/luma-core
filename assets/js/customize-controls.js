(function (wp) {
    if (typeof wp === 'undefined' || !wp.customize) return;
    const prefix = wpData.prefix;

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

    // Uncheck Shrink if Sticky is turned off
    wp.customize(`${prefix}_header_navbar_sticky`, (stickySetting) => {
        stickySetting.bind((value) => {
            if (value === false) {
                console.log('sticky set to false');
                wp.customize(`${prefix}_header_navbar_shrink`, (shrinkSetting) => {
                    if (shrinkSetting.get() == true) {
                        shrinkSetting.set(false); // auto-uncheck shrink
                    }
                });
            }
        });
    });

    // Full archive view depends on list view (not grid or masonry)
    wp.customize(`${prefix}_display_archive_view`, (displaySetting) => {
        displaySetting.bind((value) => {
            if (value === 'full') {
                wp.customize(`${prefix}_display_archive_format`, (formatSetting) => {
                    if (formatSetting.get() !== 'list') {
                        formatSetting.set('list');
                    }
                });
            }
        });
    });

    // Change full to excerpt if grid or masonry are chosen
    wp.customize(`${prefix}_display_archive_format`, (formatSetting) => {
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

    wp.customize.bind('ready', () => {

        const categories = Object.keys(wpData.categories || {});

        categories.forEach(category => {
            const buttonId = `font_reset_${category}_control`;
            const button = document.getElementById(buttonId);

            if (!button) return;
            console.log(button);
            button.addEventListener('click', () => {
                fetch(wpData.ajax, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'font_reset',
                        category: category,
                        nonce: wpData.nonce
                    })
                })
                    .then(() => wp.customize.previewer.refresh());
            });
        });

    });
})(wp);
