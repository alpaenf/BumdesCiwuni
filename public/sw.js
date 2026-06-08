self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // We only need an empty fetch handler to satisfy PWA installability criteria.
    // Let the browser handle all network requests natively to avoid caching bugs and POST errors.
});
