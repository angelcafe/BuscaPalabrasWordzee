const cacheName = 'bpwordzee';
const filesToCache = [
    './',
    './index.html',
    './front/icons/facebook.png',
    './front/icons/telegram.png',
    './front/icons/wordzee-256_256.webp',
    './front/icons/wordzee-32_32.png',
    './front/icons/wordzee-512_512.png',
    './front/icons/wordzee-64_64.jpg',
];
const version = '0.3.2';
/* Start the service worker and cache all of the app's content */
self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(cacheName).then(function (cache) {
            return cache.addAll(filesToCache);
        })
    );
    self.skipWaiting();
});

/* Serve cached content when offline */
self.addEventListener('fetch', function (e) {
    e.respondWith(
        caches.match(e.request).then(function (response) {
            return response || fetch(e.request);
        })
    );
});