const API_BASE = 'http://localhost/openSourse/back/public/api';
function apiUrl(path){ if(!path) return API_BASE; if(path.startsWith('/')) path = path.substring(1); return API_BASE.replace(/\/+$/,'') + '/' + path; }

function qs(sel, ctx=document){ return ctx.querySelector(sel); }
function qsa(sel, ctx=document){ return Array.from(ctx.querySelectorAll(sel)); }
function show(id){ qsa('main > section').forEach(s=>s.style.display='none'); const el=qs(id); if(el) el.style.display='block'; }

async function fetchJson(url, options){
  try{
    const res = await fetch(url, options);
    const ct = res.headers.get('content-type') || '';
    const text = await res.text();
    if (ct.includes('application/json') || ct.includes('text/json')) {
      try { return JSON.parse(text); } catch(err){ throw new Error('JSON inválido: '+err.message+'\n'+text.slice(0,500)); }
    }
    if (res.status === 204) return null;
    throw new Error('Esperava JSON mas recebeu outro conteúdo (status '+res.status+'):\n'+text.slice(0,500));
  }catch(err){ throw err; }
}

document.addEventListener('DOMContentLoaded', ()=>{
  show('#page-home'); bindNav(); initMap(); loadRecentPharmacies(); checkApiConnectivity();
});

function bindNav(){
  qs('#nav-search').addEventListener('click', e=>{ e.preventDefault(); show('#page-home'); });
  qs('#nav-map').addEventListener('click', e=>{ e.preventDefault(); show('#page-map'); setTimeout(()=>map.invalidateSize(),300); loadMapPharmacies(); });
  qs('#btn-login').addEventListener('click', ()=>{ new bootstrap.Modal(qs('#modalLogin')).show(); });
  qs('#form-search').addEventListener('submit', onSearch);
  qs('#form-login').addEventListener('submit', onLogin);
}

async function onSearch(e){
  e.preventDefault();
  const q = qs('#q').value.trim(); if(!q) return;
  qs('#search-results').innerHTML = '<div class="spinner-border"></div>';
  try{
    const data = await fetchJson(apiUrl('search?q='+encodeURIComponent(q)));
    renderSearchResults(data);
  }catch(err){
    qs('#search-results').innerHTML = '<div class="api-error">Erro: '+err.message+'</div>';
  }
}

function renderSearchResults(data){
  if(!data){ qs('#search-results').innerHTML = '<div class="alert alert-warning">Nenhuma resposta do servidor</div>'; return; }
  let html='';
  if(Array.isArray(data.products) && data.products.length){
    html += '<h5>Produtos</h5><ul class="list-group mb-3">';
    data.products.forEach(p=> html += `<li class="list-group-item"><strong>${escapeHtml(p.nome)}</strong> — ${escapeHtml(p.farmacia||'')}<br/><small>Preço: ${escapeHtml(p.preco)}</small></li>`);
    html += '</ul>';
  }
  if(Array.isArray(data.services) && data.services.length){
    html += '<h5>Serviços</h5><ul class="list-group">';
    data.services.forEach(s=> html += `<li class="list-group-item"><strong>${escapeHtml(s.nome)}</strong> — ${escapeHtml(s.farmacia||'')}</li>`);
    html += '</ul>';
  }
  if(!html) html = '<div class="alert alert-warning">Nenhum resultado</div>';
  qs('#search-results').innerHTML = html;
}

async function loadRecentPharmacies(){
  try{
    const json = await fetchJson(apiUrl('farmacias'));
    const list = qs('#recent-pharmacies'); list.innerHTML='';
    if(!json || !Array.isArray(json.data)) return;
    json.data.slice(0,8).forEach(f=>{
      const li = document.createElement('li'); li.className='list-group-item d-flex justify-content-between align-items-start';
      li.innerHTML = `<div><strong>${escapeHtml(f.nome)}</strong><br/><small>${escapeHtml(f.endereco||'')}</small></div><button class='btn btn-sm btn-outline-primary' onclick='openPharmacy(${Number(f.id)})'>Abrir</button>`;
      list.appendChild(li);
    });
  }catch(err){ console.error('loadRecentPharmacies',err); }
}

let map, mapMarkers=[];
function initMap(){
  map = L.map('map').setView([-8.839,13.289],7);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'© OpenStreetMap contributors' }).addTo(map);
}

async function loadMapPharmacies(){
  try{
    const json = await fetchJson(apiUrl('farmacias'));
    if(!json || !Array.isArray(json.data)) return;
    mapMarkers.forEach(m=>map.removeLayer(m)); mapMarkers=[];
    json.data.forEach(f=>{
      if(!f.latitude || !f.longitude) return;
      const lat=Number(f.latitude), lng=Number(f.longitude);
      if(isNaN(lat)||isNaN(lng)) return;
      const marker = L.marker([lat,lng]).addTo(map).bindPopup(`<strong>${escapeHtml(f.nome)}</strong><br/>${escapeHtml(f.endereco||'')}`);
      mapMarkers.push(marker);
    });
    if(mapMarkers.length) map.fitBounds(L.featureGroup(mapMarkers).getBounds(), {padding:[50,50]});
    const phList = qs('#pharm-list'); phList.innerHTML='';
    json.data.forEach(f=>{
      const li = document.createElement('li'); li.className='list-group-item d-flex justify-content-between align-items-start';
      li.innerHTML = `<div><strong>${escapeHtml(f.nome)}</strong><br/><small>${escapeHtml(f.endereco||'')}</small></div><button class='btn btn-sm btn-outline-primary' onclick='openPharmacy(${Number(f.id)})'>Abrir</button>`;
      phList.appendChild(li);
    });
  }catch(err){ console.error('loadMapPharmacies',err); qs('#pharm-list').innerHTML='<li class="list-group-item api-error">Erro ao obter farmácias: '+escapeHtml(err.message)+'</li>'; }
}

async function openPharmacy(id){
  try{
    const json = await fetchJson(apiUrl('farmacias/'+id));
    if(!json || json.error){ alert('Farmácia não encontrada'); return; }
    const f = json.data;
    show('#page-pharmacy');
    qs('#pharm-name').textContent = f.nome;
    qs('#pharm-address').textContent = f.endereco||'';
    loadPharmacyProducts(f.id);
    setTimeout(()=>{
      const container = qs('#map-detail'); container.innerHTML=''; const md = L.map('map-detail').setView([f.latitude||-8.8, f.longitude||13.2],15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(md);
      L.marker([f.latitude||-8.8, f.longitude||13.2]).addTo(md).bindPopup(f.nome).openPopup();
      qs('#btn-route').onclick = ()=> startRouteTo(f.latitude, f.longitude);
    },100);
  }catch(err){ console.error('openPharmacy',err); alert('Erro: '+err.message); }
}

async function loadPharmacyProducts(farmacia_id){
  try{
    const maybe = await fetchJson(apiUrl('produtos?farmacia_id='+encodeURIComponent(farmacia_id)));
    if(maybe && Array.isArray(maybe.data)){
      qs('#pharm-products').innerHTML=''; maybe.data.forEach(p=>{ const li=document.createElement('li'); li.className='list-group-item'; li.innerHTML=`<strong>${escapeHtml(p.nome)}</strong><br/><small>Stock: ${escapeHtml(p.stock)}</small>`; qs('#pharm-products').appendChild(li); });
    } else {
      qs('#pharm-products').innerHTML = '<li class="list-group-item">(Sem produtos / endpoint não implementado)</li>';
    }
  }catch(err){ console.warn('loadPharmacyProducts',err); qs('#pharm-products').innerHTML='<li class="list-group-item">(Carregamento indisponível)</li>'; qs('#pharm-services').innerHTML='<li class="list-group-item">(Carregamento indisponível)</li>'; }
}

function startRouteTo(lat,lng){
  if(!navigator.geolocation){ alert('Geolocalização não suportada'); return; }
  navigator.geolocation.getCurrentPosition(pos=>{
    const from=L.latLng(pos.coords.latitude,pos.coords.longitude);
    const to=L.latLng(lat,lng);
    L.Routing.control({ waypoints:[from,to], router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }), show:true }).addTo(map);
    show('#page-map');
  }, err=>{ alert('Precisa permitir geolocalização'); });
}

async function onLogin(e){
  e.preventDefault();
  const email=qs('#login-email').value.trim(), pass=qs('#login-pass').value.trim();
  qs('#login-error').textContent='';
  try{
    const data = await fetchJson(apiUrl('login'), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({email, senha: pass}) });
    if(data && data.error){ qs('#login-error').textContent = data.error; return; }
    if(!data || !data.user){ qs('#login-error').textContent = 'Resposta inesperada do servidor'; return; }
    qs('#btn-login').style.display='none'; qs('#btn-dashboard').style.display='inline-block';
    new bootstrap.Modal(qs('#modalLogin')).hide();
    sessionStorage.setItem('sofa_user', JSON.stringify(data.user));
  }catch(err){ console.error('onLogin',err); qs('#login-error').textContent='Erro no login: '+err.message; }
}

function escapeHtml(s){ if(!s && s !== 0) return ''; return String(s).replace(/[&"'<>]/g, c => ({'&':'&amp;','"':'&quot;','\'':'&#39;','<':'&lt;','>':'&gt;'}[c])); }

async function checkApiConnectivity(){
  try{
    const res = await fetch(apiUrl('farmacias'));
    const ct = res.headers.get('content-type')||'';
    if(!ct.includes('application/json') && !ct.includes('text/json')){
      const banner = document.createElement('div'); banner.className='api-error mt-2'; banner.textContent = 'Aviso: o backend respondeu com um tipo de conteúdo inesperado — verifica API_BASE e backend.'; qs('#app').insertBefore(banner, qs('#app').firstChild);
    }
  }catch(err){
    const banner = document.createElement('div'); banner.className='api-error mt-2'; banner.textContent = 'Erro de ligação ao backend: ' + err.message; qs('#app').insertBefore(banner, qs('#app').firstChild);
  }
}

window.openPharmacy = openPharmacy;
