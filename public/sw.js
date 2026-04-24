/**
 * Service Worker — POS Pantau PG Rendeng
 *
 * Strategi cache:
 * - Shell (HTML, manifest, icons): cache-first, supaya app buka cepat walau offline
 * - CDN (Tailwind, Chart.js, Leaflet): cache-first dengan fallback network
 * - API calls (/api/*): NEVER cache — data harus selalu fresh & real-time
 * - Foto/upload: network-only
 *
 * Versi cache: naikkan angkanya saat deploy versi baru supaya browser refresh.
 */
const CACHE_VERSION = 'pg-rendeng-v1';
const SHELL_CACHE = `${CACHE_VERSION}-shell`;
const CDN_CACHE = `${CACHE_VERSION}-cdn`;

// File statis yang di-cache saat install
const SHELL_FILES = [
  '/Petugas_App.html',
  '/Admin_Dashboard.html',
  '/manifest.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/icons/apple-touch-icon.png',
  '/offline.html',
];

// CDN yang boleh di-cache (match by prefix)
const CDN_ALLOWLIST = [
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net',
  'https://unpkg.com',
];

// ───────── Install: pre-cache shell ─────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(SHELL_CACHE)
      .then(cache => cache.addAll(SHELL_FILES))
      .then(() => self.skipWaiting())
      .catch(err => console.warn('[SW] Install cache failed:', err))
  );
});

// ───────── Activate: bersihkan cache versi lama ─────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => !k.startsWith(CACHE_VERSION))
            .map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

// ───────── Fetch: routing strategi ─────────
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Jangan cache method selain GET
  if (req.method !== 'GET') return;

  // API calls → network-only, NEVER cache (data SPA harus real-time)
  if (url.pathname.startsWith('/api/')) {
    return; // biarkan browser handle normal
  }

  // CDN allowlist → cache-first
  if (CDN_ALLOWLIST.some(prefix => req.url.startsWith(prefix))) {
    event.respondWith(cacheFirst(req, CDN_CACHE));
    return;
  }

  // Shell (same origin, bukan /api) → cache-first + update background
  if (url.origin === self.location.origin) {
    event.respondWith(staleWhileRevalidate(req, SHELL_CACHE));
    return;
  }

  // Lainnya: network-only
});

// ───────── Strategi helpers ─────────
async function cacheFirst(req, cacheName) {
  const cached = await caches.match(req);
  if (cached) return cached;
  try {
    const res = await fetch(req);
    if (res.ok) {
      const cache = await caches.open(cacheName);
      cache.put(req, res.clone());
    }
    return res;
  } catch (err) {
    // Offline fallback untuk navigation request
    if (req.mode === 'navigate') {
      const fallback = await caches.match('/offline.html');
      if (fallback) return fallback;
    }
    throw err;
  }
}

async function staleWhileRevalidate(req, cacheName) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(req);
  const fetchPromise = fetch(req).then(res => {
    if (res.ok) cache.put(req, res.clone());
    return res;
  }).catch(() => null);

  // Kembalikan cached kalau ada (cepat), update background
  if (cached) return cached;

  const networkRes = await fetchPromise;
  if (networkRes) return networkRes;

  // Offline + no cache = fallback
  if (req.mode === 'navigate') {
    const fb = await cache.match('/offline.html');
    if (fb) return fb;
  }
  return new Response('Offline', { status: 503, statusText: 'Offline' });
}

// ───────── Listen pesan untuk manual skipWaiting ─────────
self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
