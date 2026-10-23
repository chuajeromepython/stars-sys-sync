(() => {
    'use strict';

    const form = document.getElementById('ecdc-summary-form');
    if (!form) return;

    const scope = document.getElementById('ecdc-scope');
    const entity = document.getElementById('ecdc-scope-id');
    const status = document.getElementById('ecdc-summary-status');
    const download = document.getElementById('ecdc-summary-download');
    const button = form.querySelector('button[type="submit"]');
    const storageKey = 'stars-ecdc-summary:' + form.action;
    let polling = false;
    let timer;

    function updateScope() {
        document.getElementById('ecdc-scope-label').textContent = {
            school: 'School', district: 'District', division: 'Division',
        }[scope.value];
        if (!entity.options) return;
        for (const option of entity.options) {
            option.disabled = option.dataset.scope !== scope.value;
            option.hidden = option.disabled;
        }
        entity.value = Array.from(entity.options).find(option => !option.disabled)?.value ?? '';
    }
    scope.addEventListener('change', updateScope);
    updateScope();

    function remember(url) {
        try {
            if (url) sessionStorage.setItem(storageKey, url);
            else sessionStorage.removeItem(storageKey);
        } catch { /* Reporting remains available when browser storage is disabled. */ }
    }

    function display(report) {
        download.hidden = true;
        if (report.status === 'ready') {
            status.textContent = 'Your ECDC report is ready.';
            download.href = report.download_url;
            download.hidden = false;
            if (button) button.disabled = false;
            remember(null);
        } else if (report.status === 'failed') {
            throw new Error(report.message || 'The report could not be generated. Please try again.');
        } else {
            status.textContent = report.status === 'generating' ? 'Generating ECDC report…' : 'ECDC report queued. You can continue using this page.';
            if (button) button.disabled = true;
            remember(report.status_url);
            timer = setTimeout(() => poll(report.status_url), 3000);
        }
    }

    function fail(error) {
        clearTimeout(timer);
        status.textContent = error.message;
        if (button) button.disabled = false;
    }

    async function read(response) {
        const data = await response.json();
        if (!response.ok) {
            if (response.status === 429) throw new Error('Too many requests. Please wait one minute and try again.');
            throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to retrieve report.');
        }
        return data;
    }

    async function poll(url) {
        if (polling) return;
        polling = true;
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            display(await read(response));
        } catch (error) {
            fail(error);
        } finally {
            polling = false;
        }
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearTimeout(timer);
        button.disabled = true;
        download.hidden = true;
        status.textContent = 'Requesting ECDC report…';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            display(await read(response));
        } catch (error) {
            fail(error);
        }
    });

    try {
        const url = sessionStorage.getItem(storageKey);
        if (url && new URL(url, location.href).origin === location.origin) poll(url);
    } catch { /* A fresh request can still be submitted. */ }
})();
