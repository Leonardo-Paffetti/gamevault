/**
 * app.js
 *
 * Single responsibility: application bootstrap and event orchestration.
 * Wires GameVaultAPI (data) together with GameVaultUI (rendering).
 */

(() => {
  let currentFilters = { search: '', genre: '', platform: '' };
  let debounceTimer = null;
  let hasEverSynced = null; // becomes boolean once we know meta.total

  async function loadGames() {
    GameVaultUI.showLoading();

    try {
      const { meta, games } = await GameVaultAPI.fetchGames(currentFilters);

      GameVaultUI.updateHeaderStats(meta);
      GameVaultUI.populateFilterOptions(meta);
      hasEverSynced = meta.total > 0;

      if (meta.total === 0) {
        GameVaultUI.showEmptyDatabase();
        return;
      }

      if (games.length === 0) {
        GameVaultUI.showNoResults();
        return;
      }

      GameVaultUI.renderGames(games);
    } catch (error) {
      GameVaultUI.showError(error.message || 'Não foi possível carregar o catálogo.');
    }
  }

  async function handleSync() {
    GameVaultUI.setSyncButtonState(true);

    try {
      await GameVaultAPI.syncCatalog();
      await loadGames();
    } catch (error) {
      GameVaultUI.showError(error.message || 'A API do FreeToGame está indisponível no momento.');
    } finally {
      GameVaultUI.setSyncButtonState(false);
    }
  }

  async function handleOpenDetails(id) {
    try {
      const { game } = await GameVaultAPI.fetchGameDetail(id);
      GameVaultUI.openModal(game);
    } catch (error) {
      // A lightweight failure here shouldn't break the whole page state.
      alert(error.message || 'Não foi possível carregar os detalhes do jogo.');
    }
  }

  function bindEvents() {
    const { el } = GameVaultUI;

    el.syncButton.addEventListener('click', handleSync);

    document.getElementById('search-input').addEventListener('input', (event) => {
      clearTimeout(debounceTimer);
      const value = event.target.value.trim();

      debounceTimer = setTimeout(() => {
        currentFilters.search = value;
        loadGames();
      }, 350);
    });

    el.genreSelect.addEventListener('change', (event) => {
      currentFilters.genre = event.target.value;
      loadGames();
    });

    el.platformSelect.addEventListener('change', (event) => {
      currentFilters.platform = event.target.value;
      loadGames();
    });

    el.grid.addEventListener('click', (event) => {
      const button = event.target.closest('.js-details');
      if (button) {
        handleOpenDetails(button.dataset.id);
      }
    });

    el.modalOverlay.addEventListener('click', (event) => {
      if (event.target === el.modalOverlay || event.target.closest('.js-modal-close')) {
        GameVaultUI.closeModal();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        GameVaultUI.closeModal();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindEvents();
    loadGames();
  });
})();
