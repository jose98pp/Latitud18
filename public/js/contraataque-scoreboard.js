(() => {
    'use strict';
    const matches = document.getElementById('caMatchesContainer');
    const table = document.getElementById('caStandingsContainer');
    const ticker = document.getElementById('caTickerContainer');
    const date = document.getElementById('caMatchDate');
    if (!matches || !date) return;
    let league = document.querySelector('.ca-league-tab-btn.active')?.dataset.league || 'bolivia';
    let requestNumber = 0;
    let pending;
    let timer;
    const delay = Math.max(60, Number(matches.dataset.refresh) || 180) * 1000;

    function failure() {
        [matches, table, ticker].filter(Boolean).forEach(container => {
            // Old live badges must not survive a failed refresh as if they were current.
            container.querySelectorAll('.ca-badge-live, .ca-feed-connection').forEach(node => node.remove());
            const message = document.createElement('p');
            message.className = 'small text-warning ca-feed-connection';
            message.setAttribute('role', 'status');
            message.textContent = 'No se pudo actualizar. La información visible corresponde a la última consulta.';
            container.prepend(message);
        });
    }

    async function refresh(changed = false) {
        if (document.hidden) return;
        clearTimeout(timer);
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        const number = ++requestNumber;
        const watchdog = setTimeout(() => controller.abort(), 25000);
        if (changed) {
            matches.textContent = 'Cargando partidos…';
            if (table) table.textContent = 'Cargando posiciones…';
            if (ticker) ticker.textContent = 'Cargando marcadores…';
        }
        matches.setAttribute('aria-busy', 'true');
        try {
            const url = new URL(matches.dataset.endpoint, window.location.origin);
            url.searchParams.set('liga', league);
            url.searchParams.set('fecha', date.value);
            const response = await fetch(url, {signal: controller.signal, headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error('Respuesta no disponible');
            const data = await response.json();
            if (number !== requestNumber) return;
            if (data.liga !== league || typeof data.matches_html !== 'string' || typeof data.standings_html !== 'string') throw new Error('Respuesta incompleta');
            // Only escaped server-rendered Blade fragments enter the page; API strings are not interpolated into HTML.
            matches.innerHTML = data.matches_html;
            if (table) table.innerHTML = data.standings_html;
            if (ticker && typeof data.ticker_html === 'string') ticker.innerHTML = data.ticker_html;
            const title = document.getElementById('caStandingsLeague');
            if (title) title.textContent = data.liga_meta?.name || league;
        } catch (error) {
            if (number === requestNumber) failure();
        } finally {
            clearTimeout(watchdog);
            if (number === requestNumber) {
                matches.setAttribute('aria-busy', 'false');
                timer = setTimeout(() => refresh(), delay);
            }
        }
    }

    window.switchLeagueScoreboard = (key, button) => {
        if (![...document.querySelectorAll('.ca-league-tab-btn')].some(tab => tab.dataset.league === key)) return;
        league = key;
        document.querySelectorAll('.ca-league-tab-btn').forEach(tab => tab.classList.toggle('active', tab === button || tab.dataset.league === key));
        const title = document.getElementById('caStandingsLeague');
        if (title) title.textContent = button?.textContent.trim() || key;
        refresh(true);
    };
    date.addEventListener('change', () => { if (date.validity.valid && date.value) refresh(true); });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { clearTimeout(timer); pending?.abort(); }
        else refresh();
    });
    timer = setTimeout(() => refresh(), delay);
})();
