/**
 * api.js
 *
 * Single responsibility: talk to our own backend endpoints (api/*.php).
 * This module never talks to FreeToGame directly — only the PHP backend does.
 */

const GameVaultAPI = (() => {
  const BASE = '../api';

  async function handleResponse(response) {
    let payload;

    try {
      payload = await response.json();
    } catch {
      throw new Error('Resposta inválida do servidor.');
    }

    if (!response.ok) {
      throw new Error(payload.error || 'Ocorreu um erro inesperado.');
    }

    return payload;
  }

  return {
    /**
     * @param {{search?: string, genre?: string, platform?: string}} filters
     */
    async fetchGames(filters = {}) {
      const params = new URLSearchParams();

      if (filters.search) params.set('search', filters.search);
      if (filters.genre) params.set('genre', filters.genre);
      if (filters.platform) params.set('platform', filters.platform);

      const query = params.toString();
      const response = await fetch(`${BASE}/games.php${query ? `?${query}` : ''}`);

      return handleResponse(response);
    },

    async fetchGameDetail(id) {
      const response = await fetch(`${BASE}/game.php?id=${encodeURIComponent(id)}`);

      return handleResponse(response);
    },

    async syncCatalog() {
      const response = await fetch(`${BASE}/sync.php`, { method: 'POST' });

      return handleResponse(response);
    },
  };
})();
