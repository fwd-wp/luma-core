(function (wp) {
    if (typeof wp === 'undefined' || !wp.customize) return;

    // Shrink depends on Sticky
    wp.customize('luma_core_header_shrink', (shrinkSetting) => {
        shrinkSetting.bind((value) => { // watches the setting
            if (value === true) {
                wp.customize('luma_core_header_sticky', (stickySetting) => {
                    if (stickySetting.get() == false) { // use .get() as no need to watch
                        stickySetting.set(true); // auto-check sticky if shrink is on                    
                    }
                });
            }
        });
    });

    // Uncheck Shrink if Sticky is turned off
    wp.customize('luma_core_header_sticky', (stickySetting) => {
        stickySetting.bind((value) => {
            if (value === false) {
                console.log('sticky set to false');
                wp.customize('luma_core_header_shrink', (shrinkSetting) => {
                    if (shrinkSetting.get() == true) {
                        shrinkSetting.set(false); // auto-uncheck shrink
                    }
                });
            }
        });
    });

    // Full archive view depends on list view (not grid or masonry)
    wp.customize('luma_core_post_archive_display', (displaySetting) => {
        displaySetting.bind((value) => {
            if (value === 'full') {
                wp.customize('luma_core_post__archive_format', (formatSetting) => {
                    if (formatSetting.get() !== 'list') {
                        formatSetting.set('list');
                    }
                });
            }
        });
    });

    // Change full to excerpt if grid or masonry are chosen
    wp.customize('luma_core_post__archive_format', (formatSetting) => {
        formatSetting.bind((value) => {
            if (value !== 'list') {
                wp.customize('luma_core_post_archive_display', (displaySetting) => {
                    if (displaySetting.get() !== 'excerpt') {
                        displaySetting.set('excerpt');
                    }
                });
            }
        });
    });

    wp.customize.bind('ready', () => {

        const categories = Object.keys(fontReset.categories || {});

        categories.forEach(category => {
            const buttonId = `font_reset_${category}_control`;
            const button = document.getElementById(buttonId);

            if (!button) return;
            console.log(button);
            button.addEventListener('click', () => {
                fetch(fontReset.ajax, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'font_reset',
                        category: category,
                        nonce: fontReset.nonce
                    })
                })
                    .then(() => wp.customize.previewer.refresh());
            });
        });

    });
})(wp);
