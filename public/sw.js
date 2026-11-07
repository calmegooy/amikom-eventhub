const CACHE_NAME = 'amikomeventhub-static-v2';
const PRECACHE_URLS = [
    '/manifest.json',
    '/logo.svg',
    '/favicon.ico',
    '/icon-192.png',
    '/icon-512.png',
];

// Hanya asset publik yang boleh masuk Cache Storage. Dokumen HTML selalu
// memakai network agar halaman privat dan navbar berbasis session tidak bocor.
const CACHEABLE_PATH_PREFIXES = [
    '/build/',
    '/assets/',
];

const CACHEABLE_EXACT_PATHS = new Set(PRECACHE_URLS);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_URLS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME)
                          .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== location.origin) {
        return;
    }

    // Semua navigasi HTML, termasuk homepage yang navbarnya berbasis session,
    // tidak diintersep dan tidak pernah disimpan oleh service worker.
    if (request.mode === 'navigate' || request.destination === 'document') {
        return;
    }

    const isCacheableAsset = CACHEABLE_EXACT_PATHS.has(url.pathname)
        || CACHEABLE_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));

    if (! isCacheableAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type === 'opaque') {
                    return networkResponse;
                }
                const responseToCache = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache));

                return networkResponse;
            });
        })
    );
});
