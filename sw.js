const CACHE_NAME = 'ghost-news-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/assets/css/ghost.css',
    '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Only cache GET requests, and don't cache admin/auth routes aggressively
    if (event.request.method !== 'GET' || event.request.url.includes('/admin') || event.request.url.includes('/installer')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request).then((fetchResponse) => {
                return caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, fetchResponse.clone());
                    return fetchResponse;
                });
            });
        }).catch(() => {
            // Offline fallback
            if (event.request.mode === 'navigate') {
                return caches.match('/');
            }
        })
    );
});
