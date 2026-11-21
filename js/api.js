/**
 * Sistema DR — Cliente Fetch API (Dunay Cervantes)
 * Comunicación asíncrona entre las vistas y los endpoints PHP
 */
const SistemaAPI = {
  async postForm(url, formData) {
    const resp = await fetch(url, {
      method: 'POST',
      body: formData
    });
    return await resp.json();
  },

  async postJSON(url, payload) {
    const resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    return await resp.json();
  },

  async getJSON(url) {
    const resp = await fetch(url);
    return await resp.json();
  }
};
