const CACHE_NAME = 'uhtv-cache-v1';
const STATIC_ASSETS = [
  '/',
  '/favicon.ico',
  '/images/Logo.jpg',
  '/images/default-news.svg'
];

// Installation: Cache core static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// Activation: Clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: Network-first strategy for navigation, Stale-while-revalidate for assets
self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Ignore non-GET requests and admin/API routes
  if (request.method !== 'GET' || request.url.includes('/admin') || request.url.includes('/logout')) {
    return;
  }

  // HTML navigation requests: Network First, fallback to cache
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        })
        .catch(async () => {
          const cachedResponse = await caches.match(request);
          if (cachedResponse) {
            return cachedResponse;
          }
          return caches.match('/');
        })
    );
    return;
  }

  // Assets (images, styles, scripts): Cache first / stale-while-revalidate
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      const fetchPromise = fetch(request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
        }
        return networkResponse;
      }).catch(() => {
        // Silently fail fetch if offline
      });

      return cachedResponse || fetchPromise;
    })
  );
});
