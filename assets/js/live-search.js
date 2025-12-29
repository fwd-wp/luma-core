document.querySelectorAll('.search-field').forEach(input => {
    const dropdown = input.closest('form')?.querySelector('.search-dropdown');
    if (!dropdown) return;

    let timer;
    let controller;
    let activeIndex = -1;
    const cache = {};

    // Helper to render results
    const renderResults = results => {
        if (!results.length) {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            return;
        }

        dropdown.innerHTML = results
            .map(item => `
                <a href="${item.url}" role="option" class="search-dropdown-item">
                    <strong>${item.title}</strong>
                    ${item.price ? `<span class="price">${item.price}</span>` : ''}
                </a>
            `)
            .join('');

        dropdown.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        activeIndex = -1;
    };

    // Input event (debounced)
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const term = input.value.trim();
            if (term.length < 2) {
                renderResults([]);
                return;
            }

            // Return cached results if available
            if (cache[term]) {
                renderResults(cache[term]);
                return;
            }

            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const url = new URL(themeSearch.ajaxUrl);
                url.searchParams.set('action', 'live_search');
                url.searchParams.set('term', term);
                url.searchParams.set('nonce', themeSearch.nonce);

                const res = await fetch(url, { signal: controller.signal });
                if (!res.ok) return;

                const results = await res.json();
                cache[term] = results; // store in cache
                renderResults(results);
            } catch (err) {
                if (err.name !== 'AbortError') console.error(err);
            }
        }, 300);
    });

    // Keyboard navigation & escape
    input.addEventListener('keydown', e => {
        const items = [...dropdown.querySelectorAll('a')];
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + items.length) % items.length;
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            items[activeIndex].click();
            return;
        } else if (e.key === 'Escape') {
            dropdown.hidden = true;
            input.blur();
            return;
        } else {
            return;
        }

        // Update active item
        items.forEach(el => el.classList.remove('is-active'));
        items[activeIndex].classList.add('is-active');
        items[activeIndex].focus();
    });

    // Close dropdown on blur
    input.addEventListener('blur', () => {
        setTimeout(() => {
            dropdown.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        }, 150);
    });
});
