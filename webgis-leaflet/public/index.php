<?php
require_once __DIR__ . '/../config.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>webgis-leaflet — Dark Forest / Satellite</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div id="sidebar" class="sidebar">
    <div class="sidebar-top">
      <div class="logo">WK</div>
      <h3>WebGIS Kehutanan</h3>
    </div>

    <div class="auth-area">
      <?php if(!empty($_SESSION['user'])): ?>
        <div><strong><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong> <span class="badge bg-light text-dark"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></span></div>
        <div style="margin-top:8px;"><a href="../auth/logout.php" class="btn btn-sm btn-outline-light">Logout</a></div>
      <?php else: ?>
        <form method="post" action="../auth/login.php">
          <input name="username" class="form-control form-control-sm mb-1" placeholder="username" required>
          <input name="password" type="password" class="form-control form-control-sm mb-1" placeholder="password" required>
          <button class="btn btn-sm btn-success">Login</button>
        </form>
        <button id="showReg" class="btn btn-sm btn-outline-light mt-2">Register</button>
        <form id="regForm" method="post" action="../auth/register.php" style="display:none;margin-top:8px">
          <input name="username" class="form-control form-control-sm mb-1" placeholder="username" required>
          <input name="password" type="password" class="form-control form-control-sm mb-1" placeholder="password" required>
          <button class="btn btn-sm btn-primary">Register</button>
        </form>
      <?php endif; ?>
    </div>

    <hr class="divider">

    <div class="controls">
      <button id="btnMarker" class="btn btn-sm btn-outline-light mb-1">Tambah Marker</button>
      <button id="btnPolygon" class="btn btn-sm btn-outline-light mb-1">Gambar Polygon</button>
      <button id="btnStop" class="btn btn-sm btn-outline-danger mb-1">Stop</button>
    </div>

    <div class="save-area mt-2">
      <input id="f-name" class="form-control form-control-sm mb-1" placeholder="Nama (opsional)">
      <textarea id="f-desc" class="form-control form-control-sm mb-1" rows="2" placeholder="Deskripsi (opsional)"></textarea>
      <button id="saveBtn" class="btn btn-sm btn-success w-100">Simpan Feature</button>
    </div>

    <hr class="divider">
    <h6>Data Tersimpan</h6>
    <div id="featuresList" class="features-list">Memuat…</div>
  </div>

  <!-- Always-visible circular transparent toggle button (arrow icon) -->
  <button id="toggleSidebar" class="circle-toggle" aria-label="Toggle sidebar" title="Buka/Tutup sidebar">
    <span id="toggleIcon" class="arrow left"></span>
  </button>

  <!-- Locate button under the toggle -->
  <button id="locateBtn" class="circle-toggle small" aria-label="Lokasi saya" title="Lokasi saya">📍</button>

  <div id="map"></div>

  <script>const API_BASE = './api.php';</script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
  <script src="main.js"></script>
  <script>
    document.getElementById('showReg')?.addEventListener('click', ()=>{ const r=document.getElementById('regForm'); r.style.display = r.style.display === 'none' ? '' : 'none'; });
  </script>
</body>
</html>
