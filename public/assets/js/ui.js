/**
 * ui.js
 *
 * Single responsibility: render data into the DOM.
 * No fetch calls here — this module only receives data and paints it.
 */

const GameVaultUI = (() => {
  const el = {
    grid: document.getElementById('games-grid'),
    skeleton: document.getElementById('skeleton-grid'),
    stateEmpty: document.getElementById('state-empty'),
    stateNoResults: document.getElementById('state-no-results'),
    stateError: document.getElementById('state-error'),
    stateErrorMsg: document.getElementById('state-error-message'),
    totalCount: document.getElementById('total-count'),
    lastSync: document.getElementById('last-sync'),
    genreSelect: document.getElementById('genre-filter'),
    platformSelect: document.getElementById('platform-filter'),
    modalOverlay: document.getElementById('game-modal'),
    modalContent: document.getElementById('modal-content-inner'),
    syncButton: document.getElementById('sync-button'),
  };

  function hideAllStates() {
    el.grid.style.display = 'none';
    el.skeleton.classList.remove('is-visible');
    el.skeleton.style.display = 'none';
    [el.stateEmpty, el.stateNoResults, el.stateError].forEach((panel) => {
      panel.classList.remove('is-visible');
    });
  }

  function showLoading() {
    hideAllStates();
    el.skeleton.style.display = 'grid';
  }

  function showEmptyDatabase() {
    hideAllStates();
    el.stateEmpty.classList.add('is-visible');
  }

  function showNoResults() {
    hideAllStates();
    el.stateNoResults.classList.add('is-visible');
  }

  function showError(message) {
    hideAllStates();
    el.stateErrorMsg.textContent = message;
    el.stateError.classList.add('is-visible');
  }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString('pt-BR', { year: 'numeric', month: 'short', day: '2-digit' });
  }

  function renderGames(games) {
    hideAllStates();
    el.grid.style.display = 'grid';

    el.grid.innerHTML = games.map((game) => `
      <article class="game-card" data-id="${game.id}">
        <div class="game-thumb">
          <img src="${escapeHtml(game.thumbnail || '')}" alt="${escapeHtml(game.title)}" loading="lazy"
               onerror="this.src='https://placehold.co/460x260/1b1c28/6b6b80?text=GameVault';" />
          ${game.genre ? `<span class="game-genre-badge">${escapeHtml(game.genre)}</span>` : ''}
        </div>
        <div class="game-body">
          <h3 class="game-title">${escapeHtml(game.title)}</h3>
          <p class="game-desc">${escapeHtml(game.short_description || 'Sem descrição disponível.')}</p>
          <div class="game-meta">
            ${game.platform ? `<span>${escapeHtml(game.platform)}</span>` : ''}
            ${game.publisher ? `<span>${escapeHtml(game.publisher)}</span>` : ''}
            ${game.release_date ? `<span>${formatDate(game.release_date)}</span>` : ''}
          </div>
          <div class="game-actions">
            <a class="btn btn-primary" href="${escapeHtml(game.game_url || '#')}" target="_blank" rel="noopener noreferrer">Jogar Agora</a>
            <button class="btn btn-secondary js-details" data-id="${game.id}" type="button">Detalhes</button>
          </div>
        </div>
      </article>
    `).join('');
  }

  function updateHeaderStats(meta) {
    el.totalCount.textContent = meta.total ?? 0;
    el.lastSync.textContent = meta.last_synced_at
      ? new Date(meta.last_synced_at.replace(' ', 'T')).toLocaleString('pt-BR')
      : 'nunca sincronizado';
  }

  function populateFilterOptions(meta) {
    const buildOptions = (values, currentValue) => {
      const options = ['<option value="">Todos</option>']
        .concat(values.map((v) => `<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`));
      return options.join('');
    };

    const genreValue = el.genreSelect.value;
    const platformValue = el.platformSelect.value;

    el.genreSelect.innerHTML = buildOptions(meta.genres || []);
    el.platformSelect.innerHTML = buildOptions(meta.platforms || []);

    el.genreSelect.value = genreValue;
    el.platformSelect.value = platformValue;
  }

  function openModal(game) {
    el.modalContent.innerHTML = `
      <button class="modal-close js-modal-close" type="button">&times;</button>
      <img class="modal-thumb" src="${escapeHtml(game.thumbnail || '')}" alt="${escapeHtml(game.title)}"
           onerror="this.src='https://placehold.co/640x360/1b1c28/6b6b80?text=GameVault';" />
      <div class="modal-content">
        <h2>${escapeHtml(game.title)}</h2>
        <p class="description">${escapeHtml(game.description || game.short_description || 'Sem descrição disponível.')}</p>
        <div class="modal-info-grid">
          <div class="modal-info-item"><span>Gênero</span><strong>${escapeHtml(game.genre || '—')}</strong></div>
          <div class="modal-info-item"><span>Plataforma</span><strong>${escapeHtml(game.platform || '—')}</strong></div>
          <div class="modal-info-item"><span>Publisher</span><strong>${escapeHtml(game.publisher || '—')}</strong></div>
          <div class="modal-info-item"><span>Developer</span><strong>${escapeHtml(game.developer || '—')}</strong></div>
          <div class="modal-info-item"><span>Lançamento</span><strong>${formatDate(game.release_date)}</strong></div>
          <div class="modal-info-item"><span>Perfil FreeToGame</span><strong><a href="${escapeHtml(game.freetogame_profile_url || '#')}" target="_blank" rel="noopener noreferrer">Ver perfil</a></strong></div>
        </div>
        <div class="modal-actions">
          <a class="btn btn-primary" href="${escapeHtml(game.game_url || '#')}" target="_blank" rel="noopener noreferrer">Jogar Agora</a>
        </div>
      </div>
    `;
    el.modalOverlay.classList.add('is-open');
  }

  function closeModal() {
    el.modalOverlay.classList.remove('is-open');
  }

  function setSyncButtonState(isSyncing) {
    el.syncButton.disabled = isSyncing;
    el.syncButton.classList.toggle('is-syncing', isSyncing);
    el.syncButton.querySelector('.label').textContent = isSyncing
      ? 'Sincronizando...'
      : 'Sincronizar Catálogo';
  }

  return {
    el,
    showLoading,
    showEmptyDatabase,
    showNoResults,
    showError,
    renderGames,
    updateHeaderStats,
    populateFilterOptions,
    openModal,
    closeModal,
    setSyncButtonState,
  };
})();
