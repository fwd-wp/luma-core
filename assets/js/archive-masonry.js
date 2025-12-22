// Global instance
window.masonryInstance = window.masonryInstance || null;

window.initMasonry = function(grid) {
    return new Masonry(grid, {
        itemSelector: '.post',
        percentPosition: true,
    });
}

window.destroyMasonry = function() {
    if (window.masonryInstance && typeof window.masonryInstance.destroy === 'function') {
        window.masonryInstance.destroy();
        window.masonryInstance = null;
    }
}

// Front-end only
if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.querySelector('.archive-grid');
        const selector = document.querySelector('.is-masonry');
        if (grid && selector) {
            window.masonryInstance = window.initMasonry(grid);
        }
    });
}
