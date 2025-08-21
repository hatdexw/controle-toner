const VERSION='v2';
const CACHE_NAME=`toner-cache-${VERSION}`;
const CORE_ASSETS=[
  '/controle-toner/','/controle-toner/manifest.webmanifest','/controle-toner/dist/output.css',
  '/controle-toner/offline'
];
self.addEventListener('install',e=>{
  e.waitUntil(
    caches.open(CACHE_NAME).then(c=>c.addAll(CORE_ASSETS)).then(()=>self.skipWaiting())
  );
});
self.addEventListener('activate',e=>{
  e.waitUntil(
    caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE_NAME).map(k=>caches.delete(k)))).then(()=>self.clients.claim())
  );
});
function isHTML(req){return req.destination==='document' || (req.headers.get('accept')||'').includes('text/html');}
// Stale-While-Revalidate for CSS/JS, Cache First for images, Network First for pages
self.addEventListener('fetch',e=>{
  const req=e.request;
  if(req.method!=='GET') return;
  if(isHTML(req)){
    e.respondWith(
      fetch(req).then(res=>{
        const copy=res.clone(); caches.open(CACHE_NAME).then(c=>c.put(req,copy)); return res;
      }).catch(()=>caches.match(req).then(c=>c || caches.match('/controle-toner/offline')))
    );
    return;
  }
  if(req.destination==='style' || req.destination==='script'){
    e.respondWith(
      caches.match(req).then(cached=>{
        const fetchPromise=fetch(req).then(res=>{caches.open(CACHE_NAME).then(c=>c.put(req,res.clone())); return res;});
        return cached || fetchPromise;
      })
    );
    return;
  }
  if(req.destination==='image'||req.destination==='font'){
    e.respondWith(
      caches.match(req).then(cached=>cached || fetch(req).then(res=>{caches.open(CACHE_NAME).then(c=>c.put(req,res.clone())); return res;}).catch(()=>cached))
    );
    return;
  }
  // default: try cache then network
  e.respondWith(
    caches.match(req).then(cached=>cached || fetch(req).then(res=>{caches.open(CACHE_NAME).then(c=>c.put(req,res.clone())); return res;}))
  );
});
