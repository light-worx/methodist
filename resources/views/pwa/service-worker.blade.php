const staticCacheName = "pwa-static-v" + new Date().getTime();
const filesToCache = [
    "/",
    "/offline",
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
    if (event.request.method !== "GET") return;

    if (event.request.mode === "navigate") {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                const fetchPromise = fetch(event.request).then(networkResponse => {
                    return caches.open(staticCacheName).then(cache => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                }).catch(() => {
                    return caches.match("/offline");
                });

                return cachedResponse || fetchPromise;
            })
        );
    } else {
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

// ── Push: display notification from server payload ───────────────────
self.addEventListener("push", event => {
    let data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = {
            title: "Connexion",
            body:  event.data ? event.data.text() : "",
        };
    }

    const title   = data.title  ?? "Connexion";
    const options = {
        body:               data.body  ?? "",
        icon:               data.icon  ?? "/methodist/images/icons/android/android-launchericon-192-192.png",
        badge:              data.badge ?? "/methodist/images/icons/android/android-launchericon-96-96.png",
        data:               { url: data.url ?? "/" },
        vibrate:            [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ── Notification click: focus existing window or open a new one ──────
self.addEventListener("notificationclick", event => {
    event.notification.close();

    const targetUrl = event.notification.data?.url ?? "/";

    event.waitUntil(
        clients.matchAll({ type: "window", includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && "focus" in client) {
                    client.focus();
                    client.navigate(targetUrl);
                    return;
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});