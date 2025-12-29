document.addEventListener('click', async e => {
	const link = e.target.closest('.pagination a');
	if (!link) return;

	e.preventDefault();

	const url = new URL(link.href);

	const res = await fetch(
		`${wp.ajax.settings.url}?action=search_results&s=${url.searchParams.get('s')}`
	);

	const html = await res.text();
	document.querySelector('#search-results').innerHTML = html;

	history.pushState({}, '', link.href);
});