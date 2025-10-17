// main.js - dark forest / satellite with sidebar toggle + locate me
let map, drawnItems, drawControl, currentLayer = null, isAdmin=false;
let basemaps = {};

function initMap(){
  map = L.map('map', {zoomControl:false}).setView([-2.5489,118.0149],5);

  // Dark forest basemap (Carto Dark) as base for "dark forest" feel
  const dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {maxZoom:19, attribution:'&copy; Carto & OpenStreetMap'});
  // Satellite basemap (Esri WorldImagery)
  const sat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {maxZoom:19, attribution:'Tiles &copy; Esri'});

  basemaps['Dark Forest'] = dark;
  basemaps['Satellite'] = sat;

  // Start with satellite for realism, overlay darker layer for forest feel
  sat.addTo(map);

  // Layer control for basemap toggle
  L.control.layers(basemaps, null, {position:'topright'}).addTo(map);

  // Drawn items
  drawnItems = new L.FeatureGroup().addTo(map);
  drawControl = new L.Control.Draw({
    draw:{ polyline:false, rectangle:false, circle:false, circlemarker:false, polygon:{allowIntersection:false, showArea:true}, marker:{} },
    edit:{ featureGroup: drawnItems }
  });
  map.addControl(drawControl);

  map.on(L.Draw.Event.CREATED, function(e){ const layer = e.layer; drawnItems.addLayer(layer); currentLayer = layer; });
  map.on(L.Draw.Event.EDITED, function(e){ e.layers.eachLayer(function(l){ currentLayer = l; }); });

  // attach UI events
  document.getElementById('btnMarker').addEventListener('click', ()=> new L.Draw.Marker(map).enable());
  document.getElementById('btnPolygon').addEventListener('click', ()=> new L.Draw.Polygon(map).enable());
  document.getElementById('btnStop').addEventListener('click', ()=> { /* stop drawing via internal controls */ });

  document.getElementById('saveBtn').addEventListener('click', saveFeature);
  document.getElementById('toggleSidebar').addEventListener('click', toggleSidebar);
  document.getElementById('locateBtn').addEventListener('click', locateMe);
  document.getElementById('showReg')?.addEventListener('click', ()=>{ const r=document.getElementById('regForm'); r.style.display = r.style.display === 'none' ? '' : 'none'; });

  fetchAuthStatus();
  loadFeatures();
}

// toggle sidebar and arrow icon
function toggleSidebar(){
  const sb = document.getElementById('sidebar');
  const icon = document.getElementById('toggleIcon');
  sb.classList.toggle('hidden');
  const hidden = sb.classList.contains('hidden');
  if(hidden){ icon.classList.remove('left'); icon.classList.add('right'); } else { icon.classList.remove('right'); icon.classList.add('left'); }
}

// locate me: add marker + auto zoom
function locateMe(){
  if(!navigator.geolocation) return alert('Geolocation tidak tersedia');
  navigator.geolocation.getCurrentPosition(pos=>{
    const lat = pos.coords.latitude, lng = pos.coords.longitude;
    // remove previous temp locate marker if any
    if(window._locMarker) map.removeLayer(window._locMarker);
    window._locMarker = L.marker([lat,lng]).addTo(map).bindPopup('Ini lokasi kamu').openPopup();
    map.setView([lat,lng], 16);
  }, err=> alert('Gagal mengakses lokasi: '+err.message));
}

// auth status
async function fetchAuthStatus(){
  try{
    const res = await fetch(API_BASE + '?action=status');
    const j = await res.json();
    isAdmin = j.user && j.user.role === 'admin';
    if(!isAdmin){
      document.getElementById('saveBtn').disabled = true;
      document.getElementById('btnMarker').disabled = true;
      document.getElementById('btnPolygon').disabled = true;
    }
  }catch(e){ console.error(e); }
}

function layerToGeoJSON(layer){ return layer.toGeoJSON().geometry; }

async function saveFeature(){
  if(!isAdmin) return alert('Hanya admin yang bisa menyimpan.');
  if(!currentLayer) return alert('Belum ada feature aktif.');
  const geom = layerToGeoJSON(currentLayer);
  const payload = { type:'Feature', geometry: geom, properties:{ name: document.getElementById('f-name').value, description: document.getElementById('f-desc').value } };
  try{
    const res = await fetch(API_BASE + '?action=create', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const j = await res.json();
    if(j.success){ alert('Tersimpan (id='+j.id+')'); document.getElementById('f-name').value=''; document.getElementById('f-desc').value=''; currentLayer=null; loadFeatures(); }
    else alert('Gagal: '+(j.error||''));
  }catch(e){ console.error(e); alert('Error koneksi'); }
}

async function loadFeatures(){
  const list = document.getElementById('featuresList'); list.innerHTML = 'Memuat…';
  try{
    const res = await fetch(API_BASE + '?action=list');
    const j = await res.json();
    if(!j.success){ list.innerHTML = 'Gagal memuat'; return; }
    drawnItems.clearLayers(); list.innerHTML = '';
    j.features.forEach(f=>{
      if(!f.geojson) return;
      const g = JSON.parse(f.geojson);
      const layer = L.geoJSON(g, { style:{ color:'#8fbf87', weight:2, fillOpacity:0.35 } }).addTo(drawnItems);
      layer.bindPopup('<strong>'+escapeHtml(f.name||'')+'</strong><br/>'+escapeHtml(f.description||'')+'<br/><small>oleh: '+escapeHtml(f.created_by||'')+'</small>');
      const el = document.createElement('div'); el.className='feature-item'; el.innerHTML='<strong>'+escapeHtml(f.name||('#'+f.id))+'</strong><div class="muted">'+escapeHtml(f.description||'')+'</div>';
      const btn = document.createElement('button'); btn.textContent='Tampilkan'; btn.className='btn btn-sm btn-outline-light mt-2'; btn.onclick = ()=>{ map.fitBounds(layer.getBounds()); layer.openPopup(); };
      el.appendChild(btn);
      if(isAdmin){ const del=document.createElement('button'); del.textContent='Hapus'; del.className='btn btn-sm btn-outline-danger mt-2 ms-2'; del.onclick = ()=> deleteFeature(f.id); el.appendChild(del); }
      list.appendChild(el);
    });
  }catch(e){ list.innerHTML='Error memuat'; console.error(e); }
}

async function deleteFeature(id){
  if(!confirm('Hapus?')) return;
  const form = new FormData(); form.append('id', id);
  const res = await fetch(API_BASE + '?action=delete', { method:'POST', body: form });
  const j = await res.json();
  if(j.success){ alert('Dihapus'); loadFeatures(); } else alert('Gagal hapus: '+(j.error||''));
}

function escapeHtml(s){ if(!s) return ''; return s.toString().replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; }); }

document.addEventListener('DOMContentLoaded', initMap);
