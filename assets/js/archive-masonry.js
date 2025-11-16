let masonryInstance = null;

function initMasonry(grid) {
    return new Masonry(grid, {
        itemSelector: '.post',
        percentPosition: true,
    });
}

function destroyMasonry(instance) {
    if (instance && typeof instance.destroy === 'function') {
        instance.destroy();
    }
}

/**
 * Apply archive layout classes + Masonry handling in customize
 */
function applyArchiveLayout(value) {
    const grid = document.querySelector('.archive-grid');
    if (!grid) return;

    // Clean up
    destroyMasonry(masonryInstance);
    masonryInstance = null;
    grid.classList.remove('archive-grid--masonry', 'archive-grid--grid');

    // Apply new mode
    if (value === 'masonry') {
        grid.classList.add('archive-grid--masonry');
        masonryInstance = initMasonry(grid);
    } else if (value === 'grid') {
        grid.classList.add('archive-grid--grid');
    }
}

/**
 * ---- Front-end only ----
 * Run Masonry once if archive is already masonry
 */
if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.querySelector('.archive-grid--masonry');
        if (grid) {
            masonryInstance = initMasonry(grid);
        }
    });
}

/**
 * ---- Customizer live preview ----
 * Bind to setting changes
 */
if (typeof wp !== 'undefined' && wp.customize) {
    wp.customize('twenty_one_post__archive_format', (setting) => {
        applyArchiveLayout(setting.get()); // init current
        setting.bind((value) => {
            applyArchiveLayout(value); // update on change
        });
    });
}
