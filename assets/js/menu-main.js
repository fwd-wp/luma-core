document.addEventListener('DOMContentLoaded', () => {
    const menu = document.querySelector('#menu-main');
    const menuButton = document.getElementById('btn-menu-toggle');
    const desktopBreakpoint = 1024;
    let desktopHoverListeners = [];
    let desktopClickListeners = [];

    function supportsHover() {
        return window.matchMedia('(hover: hover)').matches;
    }

    function initMobileMenu() {
        if (!menu || !menuButton) return;

        menuButton.addEventListener('click', toggleMenu);

        menu.querySelectorAll('.submenu-toggle').forEach(button => {
            button.addEventListener('click', submenuClickHandler);
        });
    }

    function destroyMobileMenu() {
        if (!menu || !menuButton) return;

        menu.classList.remove('is-expanded');
        menuButton.setAttribute('aria-expanded', 'false');

        menuButton.removeEventListener('click', toggleMenu);

        menu.querySelectorAll('.submenu-toggle').forEach(button => {
            button.removeEventListener('click', submenuClickHandler);
        });

        menu.querySelectorAll('.sub-menu').forEach(sub => sub.classList.remove('is-expanded'));
        menu.querySelectorAll('.submenu-toggle').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    }

    function initDesktopHover() {
        if (!menu) return;

        menu.querySelectorAll('.menu-item-has-children').forEach(li => {
            const button = li.querySelector('.submenu-toggle');
            if (!button) return;

            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-haspopup', 'true');

            const mouseEnter = () => {
                button.setAttribute('aria-expanded', 'true');
                const submenu = document.getElementById(button.getAttribute('aria-controls'));
                if (submenu) submenu.classList.add('is-expanded');
            };

            const mouseLeave = () => {
                button.setAttribute('aria-expanded', 'false');
                const submenu = document.getElementById(button.getAttribute('aria-controls'));
                if (submenu) submenu.classList.remove('is-expanded');
            };

            li.addEventListener('mouseenter', mouseEnter);
            li.addEventListener('mouseleave', mouseLeave);

            desktopHoverListeners.push({ li, mouseEnter, mouseLeave });
        });
    }

    function destroyDesktopHover() {
        desktopHoverListeners.forEach(listener => {
            listener.li.removeEventListener('mouseenter', listener.mouseEnter);
            listener.li.removeEventListener('mouseleave', listener.mouseLeave);
        });
        desktopHoverListeners = [];
    }

    function initDesktopClickFallback() {
        if (!menu) return;

        menu.querySelectorAll('.submenu-toggle').forEach(button => {
            const clickHandler = (e) => {
                e.preventDefault();
                toggleSubmenu(button);
            };
            button.addEventListener('click', clickHandler);
            desktopClickListeners.push({ button, clickHandler });
        });
    }

    function destroyDesktopClickFallback() {
        desktopClickListeners.forEach(listener => {
            listener.button.removeEventListener('click', listener.clickHandler);
        });
        desktopClickListeners = [];
    }

    function toggleMenu() {
        const expanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', !expanded);
        menu.classList.toggle('is-expanded');
    }

    function toggleSubmenu(button) {
        const submenuId = button.getAttribute('aria-controls');
        const submenu = document.getElementById(submenuId);
        if (!submenu) return;

        const expanded = button.getAttribute('aria-expanded') === 'true';

        const parentLi = button.closest('li');
        if (parentLi) {
            const siblingButtons = Array.from(parentLi.parentElement.children)
                .filter(li => li !== parentLi)
                .map(li => li.querySelector('.submenu-toggle'))
                .filter(Boolean);

            siblingButtons.forEach(sibBtn => {
                const sibSubmenu = document.getElementById(sibBtn.getAttribute('aria-controls'));
                sibBtn.setAttribute('aria-expanded', 'false');
                if (sibSubmenu) sibSubmenu.classList.remove('is-expanded');
            });
        }

        button.setAttribute('aria-expanded', !expanded);
        submenu.classList.toggle('is-expanded');
    }

    function submenuClickHandler(e) {
        e.preventDefault();
        toggleSubmenu(e.currentTarget);
    }

    function checkWidth() {
        if (window.innerWidth < desktopBreakpoint) {
            destroyDesktopHover();
            destroyDesktopClickFallback();
            initMobileMenu();
        } else {
            destroyMobileMenu();
            if (supportsHover()) {
                initDesktopHover();
                destroyDesktopClickFallback();
            } else {
                destroyDesktopHover();
                initDesktopClickFallback();
            }
        }
    }

    checkWidth(); // Run on load
    window.addEventListener('resize', checkWidth); // Run on resize
});









(function () {
    let handler;
    const header = document.querySelector('.site-header');

    window.initNavbarShrink = function () {
        if (handler) return; // already bound
        handler = function () {
            if (!header) return;
            if (window.scrollY > 50) {
                header.classList.add('is-shrunk');
            } else {
                header.classList.remove('is-shrunk');
            }
        };
        window.addEventListener('scroll', handler);
    };

    window.destroyNavbarShrink = function () {
        if (!handler) return;
        window.removeEventListener('scroll', handler);
        handler = null;
    };

    // If this script is enqueued on frontend (not Customizer), init immediately
    if (header.classList.contains('is-shrink-enabled')) {
        window.initNavbarShrink();
    }
})();
