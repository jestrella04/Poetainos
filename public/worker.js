self.importScripts("https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js");
const r = "po-cache", f = [{"revision":"28418b6c9fee7fc7d7b2f2b1fdbca40f","url":"build/assets/app-sTfp0ZdD.css"},{"revision":"ed0c862094c41f455cdef837aa7dafd2","url":"build/assets/workbox-window.prod.es5-prqDwDSL.js"}], l = "offline";
self.addEventListener("message", (e) => {
  e.origin == location.host && e.data && e.data.type === "SKIP_WAITING" && self.skipWaiting();
});
self.workbox.precaching.precacheAndRoute(f);
self.workbox.precaching.cleanupOutdatedCaches();
self.addEventListener("install", async (e) => {
  e.waitUntil(caches.open(r).then((t) => t.add(l)));
});
self.workbox.navigationPreload.isSupported() && self.workbox.navigationPreload.enable();
self.addEventListener("fetch", (e) => {
  e.request.mode === "navigate" && e.respondWith(
    (async () => {
      try {
        const t = await e.preloadResponse;
        return t || await fetch(e.request);
      } catch {
        return await (await caches.open(r)).match(l);
      }
    })()
  );
});
function d(e) {
  return self.registration.showNotification(e.title, e);
}
self.addEventListener("push", (e) => {
  self.Notification && self.Notification.permission === "granted" && e.data && e.waitUntil(d(e.data.json()));
});
self.addEventListener("notificationclick", (e) => {
  const t = e.notification;
  let i = null;
  i = t.actions[0].action;
  const a = self.clients.matchAll({
    type: "window",
    includeUncontrolled: !0
  }).then((s) => {
    let n = null;
    for (let o = 0; o < s.length; o++) {
      const c = s[o];
      if (c.url === i) {
        n = c;
        break;
      }
    }
    return n ? n.focus() : self.clients.openWindow(i);
  });
  e.waitUntil(a), t.close();
});
