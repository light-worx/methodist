const staticCacheName = "pwa-static-v" + new Date().getTime();
const filesToCache = [
    "/",                // homepage
    "/offline",         // offline fallback page (Laravel route)
    "{{ asset('methodist/css/bootstrap.min.css') }}",
    "{{ asset('methodist/js/bootstrap.min.js') }}",
    "{{ asset('methodist/images/icons/android/android-launchericon-192-192.png') }}",
    "{{ asset('methodist/images/icons/android/android-launchericon-512-512.png') }}",
    "{{ asset('methodist/images/icons/ios/512.png') }}"
];

// Install: pre-cache core files
self.addEventListener("install", event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then(cache => cache.addAll(filesToCache))
    );
});

// Activate: clear old caches
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name.startsWith("pwa-") && name !== staticCacheName)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch: SWR for HTML pages, cache-first (with background update) for assets
self.addEventListener("fetch", event => {
    if (event.request.method !== "GET") return; // don’t cache POST etc.

    if (event.request.mode === "navigate") {
        // SWR for dynamic pages
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    return caches.open(staticCacheName).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                }).catch(() => {
                    // Offline fallback if no network
                    return caches.match("/offline");
                });

                return cachedResponse || fetchPromise;
            })
        );
    } else {
        // Static assets (CSS, JS, images) → cache-first with background update
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    return caches.open(staticCacheName).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
    }
});
